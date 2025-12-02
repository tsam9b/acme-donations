<?php

namespace App\Http\Controllers;

use App\Mail\DonationReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class DonationController extends Controller
{
    /**
     * Display a listing of donations (All Donations).
     */
    public function index(Request $request)
    {
        // Show all donations (not restricted to campaigns owned by the current user)
        $query = DB::table('donations')
            ->leftJoin('users', 'donations.user_id', '=', 'users.id')
            ->join('campaigns', 'donations.campaign_id', '=', 'campaigns.id')
            ->select([
                'donations.*',
                'campaigns.name as campaign_name',
                // Prefer the registered user's name/email when available
                    // If the donor is a registered user use their name/email, otherwise fall back to empty
                    DB::raw("COALESCE(users.name, '') as donor_user_name"),
                    DB::raw("COALESCE(users.email, '') as donor_user_email"),
            ]);

        // Search across donor name, donor email, campaign name or message
        if ($request->has('search') && $request->search) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                // Search registered donor name/email, campaign name, or amount
                $sub->where('users.name', 'like', "%{$q}%")
                    ->orWhere('users.email', 'like', "%{$q}%")
                    ->orWhere('campaigns.name', 'like', "%{$q}%")
                    ->orWhere('donations.amount', 'like', "%{$q}%");

                // Only search donations.message if the column exists in the current DB
                if (Schema::hasColumn('donations', 'message')) {
                    $sub->orWhere('donations.message', 'like', "%{$q}%");
                }
            });
        }

        // Filter by campaign (campaign_id)
        if ($request->has('campaign_id') && $request->campaign_id) {
            $query->where('donations.campaign_id', $request->campaign_id);
        }

        // Date filters
        if ($request->has('start_date') && $request->start_date) {
            $query->where('donations.donation_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('donations.donation_date', '<=', $request->end_date);
        }

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50, 100]) ? $perPage : 10;

        // Sorting params
        $sortBy = $request->get('sort_by');
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Map friendly sort keys to actual columns
        $sortMap = [
            'donor' => 'users.name',
            'amount' => 'donations.amount',
            'campaign_name' => 'campaigns.name',
            'donation_date' => 'donations.donation_date',
            'created_at' => 'donations.created_at',
        ];

        if ($sortBy && array_key_exists($sortBy, $sortMap)) {
            $donationsQuery = $query->orderBy($sortMap[$sortBy], $sortDir);
        } else {
            $donationsQuery = $query->orderBy('donations.created_at', 'desc');
        }

        $donations = $donationsQuery
            ->paginate($perPage)
            ->withQueryString();

        // Return list of all campaigns for campaign filter dropdown
        $campaigns = DB::table('campaigns')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return inertia('Dashboard/Donations/Donations', [
            'donations' => $donations,
            'campaigns' => $campaigns,
            'filters' => [
                'search' => $request->search,
                'campaign_id' => $request->campaign_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'per_page' => $perPage,
                'sort_by' => $request->sort_by,
                'sort_dir' => $request->sort_dir,
            ],
        ]);
    }
    public function store(Request $request, \App\Payments\PaymentInterface $payment)
    {
        $validated = $request->validate([
            'campaign_id' => ['required', 'integer', Rule::exists('campaigns', 'id')],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string'],
        ]);

        // Check if campaign accepts donations (must be active)
        $campaign = DB::table('campaigns')->where('id', $validated['campaign_id'])->first();
        if (!$campaign || in_array($campaign->status, ['inactive', 'cancelled', 'completed'])) {
            return back()->withErrors([
                'campaign' => 'This campaign is not currently accepting donations.'
            ]);
        }

        $now = now();
        $donationId = null;

        // If a payment method was provided, attempt a charge via the payment service.
        $paymentResult = null;
        if (!empty($validated['payment_method'])) {
            try {
                $paymentResult = $payment->createCharge([
                    'amount' => $validated['amount'],
                    'currency' => 'USD',
                    'description' => 'Donation to campaign ' . $validated['campaign_id'],
                    'method' => $validated['payment_method'],
                ]);
            } catch (\Exception $e) {
                // Log and continue — do not block creating a donation for the dummy flow
                \Log::error('Payment processing failed: ' . $e->getMessage());
                $paymentResult = ['success' => false, 'transaction_id' => null, 'raw' => null];
            }
        }

        DB::transaction(function () use ($validated, $now, &$donationId, $paymentResult) {
            // Insert donation and get the ID
            $payload = [
                'user_id' => Auth::id(),
                'campaign_id' => $validated['campaign_id'],
                'amount' => $validated['amount'],
                'donation_date' => date('Y-m-d'),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Optionally store transaction id / status if columns exist
            if (Schema::hasColumn('donations', 'transaction_id') && is_array($paymentResult) && isset($paymentResult['transaction_id'])) {
                $payload['transaction_id'] = $paymentResult['transaction_id'];
            }

            if (Schema::hasColumn('donations', 'payment_status') && is_array($paymentResult) && isset($paymentResult['success'])) {
                $payload['payment_status'] = $paymentResult['success'] ? 'paid' : 'failed';
            }

            $donationId = DB::table('donations')->insertGetId($payload);

            // Update campaign current_amount
            DB::table('campaigns')->where('id', $validated['campaign_id'])->increment('current_amount', $validated['amount']);

            // Check if goal is reached and update status to completed
            $campaign = DB::table('campaigns')->where('id', $validated['campaign_id'])->first();
            if ($campaign && $campaign->goal_amount > 0 && $campaign->current_amount >= $campaign->goal_amount) {
                DB::table('campaigns')
                    ->where('id', $validated['campaign_id'])
                    ->update(['status' => 'completed', 'updated_at' => $now]);
            }
        });

        // Get donation, campaign, and donor data for email
        $donation = DB::table('donations')->where('id', $donationId)->first();
        $campaign = DB::table('campaigns')->where('id', $validated['campaign_id'])->first();
        $donor = DB::table('users')->where('id', Auth::id())->first();

        // Send thank you email to the donor
        if ($donor && $donor->email) {
            try {
                Mail::to($donor->email)->send(new DonationReceived($donation, $campaign, $donor));
            } catch (\Exception $e) {
                // Log the error but don't fail the donation
                \Log::error('Failed to send donation thank you email: ' . $e->getMessage());
            }
        }

        // For Inertia requests, redirect back (this allows onSuccess callback to fire)
        return back();
    }
}
