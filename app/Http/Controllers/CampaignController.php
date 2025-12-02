<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DB::table('campaigns')
            ->where('user_id', Auth::id());

        // Apply search filter if provided
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Apply status filter if provided
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Apply start date filter if provided
        if ($request->has('start_date') && $request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        // Apply end date filter if provided
        if ($request->has('end_date') && $request->end_date) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Get per_page value, default to 10, max 100
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50, 100]) ? $perPage : 10;

        // Sorting params and mapping to actual columns
        $sortBy = $request->get('sort_by');
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $sortMap = [
            'name' => 'campaigns.name',
            'goal_amount' => 'campaigns.goal_amount',
            'current_amount' => 'campaigns.current_amount',
            'status' => 'campaigns.status',
            'end_date' => 'campaigns.end_date',
            'created_at' => 'campaigns.created_at',
        ];

        if ($sortBy && array_key_exists($sortBy, $sortMap)) {
            $campaignsQuery = $query->orderBy($sortMap[$sortBy], $sortDir);
        } else {
            $campaignsQuery = $query->orderBy('campaigns.created_at', 'desc');
        }

        $campaigns = $campaignsQuery
            ->paginate($perPage)
            ->withQueryString(); // Preserve query parameters in pagination links

        return inertia('Dashboard/MyCampaigns/Edit', [
            'campaigns' => $campaigns,
            'predefinedAmounts' => SettingsService::getPredefinedAmounts(),
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'per_page' => $perPage,
                'sort_by' => $request->sort_by,
                'sort_dir' => $request->sort_dir,
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'goal_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => ['required', Rule::in(['active', 'inactive', 'completed', 'cancelled'])]
        ]);

        $currentTime = now();

        DB::table('campaigns')->insert([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'goal_amount' => $validated['goal_amount'] ?? 0,
            'current_amount' => 0,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'created_at' => $currentTime,
            // updated_at is intentionally omitted - it should only be set on updates
        ]);

    return redirect()->route('campaigns.index', (array) request()->only(['search', 'status', 'start_date', 'end_date', 'per_page', 'sort_by', 'sort_dir']))
            ->with('success', 'Campaign created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'goal_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => ['required', Rule::in(['active', 'inactive', 'completed', 'cancelled'])]
        ]);

        DB::table('campaigns')
            ->where('id', $id)
            ->where('user_id', Auth::id()) // Ensure user can only update their own campaigns
            ->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'goal_amount' => $validated['goal_amount'] ?? 0,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

    return redirect()->route('campaigns.index', (array) request()->only(['search', 'status', 'start_date', 'end_date', 'per_page', 'sort_by', 'sort_dir']))
            ->with('success', 'Campaign updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::table('campaigns')
            ->where('id', $id)
            ->where('user_id', Auth::id()) // Ensure user can only delete their own campaigns
            ->delete();

    return redirect()->route('campaigns.index', (array) request()->only(['search', 'status', 'start_date', 'end_date', 'per_page', 'sort_by', 'sort_dir']))
            ->with('success', 'Campaign deleted successfully!');
    }
}
