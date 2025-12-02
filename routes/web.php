<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DonationController;
use App\Services\SettingsService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

Route::get('/', function () {
    // Get available statuses from settings (default to all if not set)
    $availableStatuses = SettingsService::get('availableStatus', ['active', 'inactive', 'completed', 'cancelled']);

    // Fetch campaigns for public display, restricted to available statuses
    $query = DB::table('campaigns');

    // Restrict campaigns to only available statuses
    if (!empty($availableStatuses)) {
        $query->whereIn('status', $availableStatuses);
    }

    // Apply status filter if provided
    if (request()->has('status') && request()->status && request()->status !== 'all') {
        // Only apply filter if the requested status is in available statuses
        if (in_array(request()->status, $availableStatuses)) {
            $query->where('status', request()->status);
        }
    }

    // Apply search filter if provided
    if (request()->has('search') && request()->search) {
        $query->where('name', 'like', '%' . request()->search . '%');
    }

    // Apply start date filter if provided
    if (request()->has('start_date') && request()->start_date) {
        $query->where('start_date', '>=', request()->start_date);
    }

    // Apply end date filter if provided
    if (request()->has('end_date') && request()->end_date) {
        $query->where('end_date', '<=', request()->end_date);
    }

    // Get per_page value, default to 12 for better grid layout, max 100
    $perPage = request()->get('per_page', 12);
    $perPage = in_array($perPage, [6, 12, 24, 48]) ? $perPage : 12;

    // Only include campaigns that have not ended (either no end_date or end_date in the future).
    // Group these conditions so the status filter above applies to both clauses.
    $query->where(function ($q) {
        $q->whereNull('end_date')
          ->orWhere('end_date', '>=', now());
    });

    $campaigns = $query->orderBy('created_at', 'desc')
        ->paginate($perPage)
        ->withQueryString();

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'campaigns' => $campaigns,
        'predefinedAmounts' => SettingsService::getPredefinedAmounts(),
        'availableStatuses' => $availableStatuses,
        'welcomeTitle' => SettingsService::get('messages.welcome.title', 'Welcome to Acme Donations!'),
        'welcomeSubtitle' => SettingsService::get('messages.welcome.subtitle', 'Support a campaign and make a difference.'),
        'filters' => [
            'search' => request()->search,
            'status' => request()->status,
            'start_date' => request()->start_date,
            'end_date' => request()->end_date,
            'per_page' => $perPage
        ]
    ]);
})->name('homepage');

Route::get('/dashboard', function () {
    $query = DB::table('campaigns')
        ->where('user_id', Auth::id());

    // Apply search filter if provided
    if (request()->has('search') && request()->search) {
        $query->where('name', 'like', '%' . request()->search . '%');
    }

    // Apply status filter if provided
    if (request()->has('status') && request()->status && request()->status !== 'all') {
        $query->where('status', request()->status);
    }

    // Apply start date filter if provided
    if (request()->has('start_date') && request()->start_date) {
        $query->where('start_date', '>=', request()->start_date);
    }

    // Apply end date filter if provided
    if (request()->has('end_date') && request()->end_date) {
        $query->where('end_date', '<=', request()->end_date);
    }

    // Get per_page value, default to 10, max 100
    $perPage = request()->get('per_page', 10);
    $perPage = in_array($perPage, [5, 10, 25, 50, 100]) ? $perPage : 10;
    // Sorting params and mapping to actual columns
    $sortBy = request()->get('sort_by');
    $sortDir = strtolower(request()->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

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

    $campaigns = $campaignsQuery->paginate($perPage)->withQueryString(); // Preserve query parameters in pagination links

    return Inertia::render('Dashboard', [
        'campaigns' => $campaigns,
        'predefinedAmounts' => SettingsService::getPredefinedAmounts(),
        'filters' => [
            'search' => request()->search,
            'status' => request()->status,
            'start_date' => request()->start_date,
            'end_date' => request()->end_date,
            'per_page' => $perPage,
            'sort_by' => request()->sort_by,
            'sort_dir' => request()->sort_dir,
        ]
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Campaign routes
    Route::get('/my-campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::patch('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');

    // All campaigns (admin) - return all campaigns, not restricted by user
    Route::get('/campaigns-all', function () {
        $query = DB::table('campaigns')
            ->leftJoin('users', 'campaigns.user_id', '=', 'users.id')
            ->select([
                'campaigns.*',
                DB::raw("COALESCE(users.name, '') as owner_name"),
                DB::raw("COALESCE(users.email, '') as owner_email"),
            ]);

        if (request()->has('search') && request()->search) {
            $query->where('campaigns.name', 'like', '%' . request()->search . '%');
        }

        if (request()->has('start_date') && request()->start_date) {
            $query->where('campaigns.start_date', '>=', request()->start_date);
        }

        if (request()->has('end_date') && request()->end_date) {
            $query->where('campaigns.end_date', '<=', request()->end_date);
        }

        // Apply status filter if provided
        if (request()->has('status') && request()->status && request()->status !== 'all') {
            $query->where('campaigns.status', request()->status);
        }

        $perPage = request()->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50, 100]) ? $perPage : 10;

        // Sorting params and mapping to actual columns
        $sortBy = request()->get('sort_by');
        $sortDir = strtolower(request()->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $sortMap = [
            'name' => 'campaigns.name',
            'goal_amount' => 'campaigns.goal_amount',
            'current_amount' => 'campaigns.current_amount',
            'status' => 'campaigns.status',
            'end_date' => 'campaigns.end_date',
            'created_at' => 'campaigns.created_at',
            'owner' => 'users.name',
        ];

        if ($sortBy && array_key_exists($sortBy, $sortMap)) {
            $campaigns = $query->orderBy($sortMap[$sortBy], $sortDir)
                ->paginate($perPage)
                ->withQueryString();
        } else {
            $campaigns = $query->orderBy('campaigns.created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        }

        return Inertia::render('Dashboard/Campaigns/Index', [
            'campaigns' => $campaigns,
            'filters' => [
                'search' => request()->search,
                'status' => request()->status,
                'start_date' => request()->start_date,
                'end_date' => request()->end_date,
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    })->middleware(['admin'])->name('campaigns.all');

    // Keep the old mycampaigns route for backwards compatibility
    Route::get('/my-campaigns-old', function () {
        return Inertia::render('Dashboard/MyCampaigns/Edit');
    })->name('mycampaigns');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Donations
    Route::get('/all-donations', [DonationController::class, 'index'])->name('donations.index')->middleware(['admin']);
    Route::post('/donations', [\App\Http\Controllers\DonationController::class, 'store'])->name('donations.store');

    // My Donations - donations performed by the current authenticated user
    Route::get('/my-donations', function (\Illuminate\Http\Request $request) {
        $query = DB::table('donations')
            ->leftJoin('users', 'donations.user_id', '=', 'users.id')
            ->join('campaigns', 'donations.campaign_id', '=', 'campaigns.id')
            ->where('donations.user_id', Auth::id())
            ->select([
                'donations.*',
                'campaigns.name as campaign_name',
                DB::raw("COALESCE(users.name, '') as donor_user_name"),
                DB::raw("COALESCE(users.email, '') as donor_user_email"),
            ]);

        if ($request->has('search') && $request->search) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('users.name', 'like', "%{$q}%")
                    ->orWhere('users.email', 'like', "%{$q}%")
                    ->orWhere('campaigns.name', 'like', "%{$q}%")
                    ->orWhere('donations.amount', 'like', "%{$q}%");

                if (\Illuminate\Support\Facades\Schema::hasColumn('donations', 'message')) {
                    $sub->orWhere('donations.message', 'like', "%{$q}%");
                }
            });
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->where('donations.donation_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('donations.donation_date', '<=', $request->end_date);
        }

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50, 100]) ? $perPage : 10;

        $sortBy = $request->get('sort_by');
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

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

        $donations = $donationsQuery->paginate($perPage)->withQueryString();

        $campaigns = DB::table('campaigns')
            ->where('user_id', Auth::id())
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Dashboard/MyDonations/Donations', [
            'donations' => $donations,
            'campaigns' => $campaigns,
            'filters' => [
                'search' => $request->search,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'per_page' => $perPage,
                'sort_by' => $request->sort_by,
                'sort_dir' => $request->sort_dir,
            ],
        ]);
    })->name('mydonations.index');

    // Settings editor - view and update app settings stored as JSON in `settings.preferences` column
    Route::get('/settings', function () {
        // table uses a `preferences` json column; take the first row if present
        $row = DB::table('settings')->first();
        $settings = $row ? json_decode($row->preferences, true) : [];

        return Inertia::render('Dashboard/Settings/Edit', [
            'settings' => $settings,
        ]);
    })->middleware(['admin'])->name('settings.edit');

    Route::post('/settings', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'settings' => 'required|array',
        ]);

        $row = DB::table('settings')->first();
        $payload = ['preferences' => json_encode($data['settings']), 'updated_at' => now()];

        if ($row) {
            DB::table('settings')->where('id', $row->id)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('settings')->insert($payload);
        }

        return back()->with('success', 'Settings saved.');
    })->middleware(['admin'])->name('settings.update');
});

require __DIR__.'/auth.php';
