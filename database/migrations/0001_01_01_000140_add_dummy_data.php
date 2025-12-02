<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert some dummy users
        $now = now();

        $now = now();

        DB::table('settings')->insertGetId([
            'preferences' => '{"messages": {"welcome": {"title": "Welcome to Acme Donations!", "subtitle": "Support a campaign and make a difference."}}, "availableStatus": ["active", "inactive", "completed", "cancelled"], "predefinedAmounts": [10, 25, 50, 100]}',
            'created_at' => $now,
            'updated_at' => null,
        ]);

        $companyNames = [
            'Acme Corp',
            'GreenLeaf Solutions',
            'BrightPath Innovations',
            'Community Impact LLC',
            'BlueSky Ventures'
        ];

        $companyIds = [];
        foreach ($companyNames as $companyName) {
            $companyIds[] = DB::table('companies')->insertGetId([
                'name' => $companyName,
                'created_at' => $now,
                'updated_at' => null,
            ]);
        }

        $users = [
            [
                'name' => 'Alice Admin',
                'email' => 'alice.admin@example.test',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ],
            [
                'name' => 'Bob Builder',
                'email' => 'bob@example.test',
                'password' => bcrypt('password'),
                'role' => 'user'
            ],
            [
                'name' => 'Cara Contributor',
                'email' => 'cara@example.test',
                'password' => bcrypt('password'),
                'role' => 'user'
            ],
            [
                'name' => 'Dan Donor',
                'email' => 'dan@example.test',
                'password' => bcrypt('password'),
                'role' => 'user'
            ],
        ];

        $userIds = [];
        foreach ($users as $index => $u) {
            $u['company_id'] = $companyIds[$index % count($companyIds)];
            $u['created_at'] = $now;
            $u['updated_at'] = null;

            $userIds[] = DB::table('users')->insertGetId($u);
        }


        // Create 18 campaigns distributed among Bob, Cara and Alice
        $campaignNames = [
            'Clean Water Drive','Books for Schools','Community Garden','Animal Shelter Support','Youth Coding Classes',
            'Local Clinic Equipment','Tree Planting','Senior Care Fund','Emergency Relief','Arts in the Park',
            'Music Scholarship','Sports Equipment','Food Pantry','Refugee Support','Public Library Repair',
            'Neighborhood Safety Lights','Park Benches','Community WiFi'
        ];

        $campaignIds = [];
        $now = now();
        foreach ($campaignNames as $i => $name) {
            // rotate owners: Bob (index 1), Cara (index 2), Alice (index 0)
            $ownerIndex = [$userIds[1], $userIds[2], $userIds[0]][$i % 3];
            $goal = rand(500, 20000);
            $current = rand(0, intval($goal * 0.9));
            $start = now()->subDays(rand(0, 120))->toDateString();
            $end = now()->addDays(rand(10, 120))->toDateString();
            $statuses = ["active","inactive","completed","cancelled"];
            $today = now();

            $campaignIds[] = DB::table('campaigns')->insertGetId([
                'user_id' => $ownerIndex,
                'name' => $name,
                'description' => Str::limit('Dummy campaign for ' . $name . ' to help testing and local development.', 200),
                'goal_amount' => $goal,
                'current_amount' => $current,
                'start_date' => $start,
                'end_date' => $end,
                'status' => $statuses[array_rand($statuses)],
                'created_at' => $now,
                'updated_at' => null,
            ]);

        }

        // Create ~22 donations across campaigns from Dan and Bob & Cara as donors
        $donorUserIds = [$userIds[3], $userIds[1], $userIds[2]]; // Dan, Bob, Cara
        for ($i = 0; $i < 22; $i++) {
            $campaignId = $campaignIds[array_rand($campaignIds)];
            $donorId = $donorUserIds[array_rand($donorUserIds)];
            $amount = rand(5, 500) + (rand(0,99)/100);
            $donationDate = now()->subDays(rand(0, 200))->toDateString();

            DB::table('donations')->insert([
                'user_id' => $donorId,
                'campaign_id' => $campaignId,
                'amount' => number_format($amount, 2, '.', ''),
                'donation_date' => $donationDate,
                'created_at' => $now,
                'updated_at' => null,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove dummy donations and campaigns and users inserted by this migration
        DB::table('donations')->whereIn('campaign_id', function ($q) {
            $q->select('id')->from('campaigns')->where('name', 'like', '%Dummy campaign%');
        })->delete();

        DB::table('campaigns')->whereIn('name', [
            'Clean Water Drive','Books for Schools','Community Garden','Animal Shelter Support','Youth Coding Classes',
            'Local Clinic Equipment','Tree Planting','Senior Care Fund','Emergency Relief','Arts in the Park',
            'Music Scholarship','Sports Equipment','Food Pantry','Refugee Support','Public Library Repair',
            'Neighborhood Safety Lights','Park Benches','Community WiFi'
        ])->delete();

        DB::table('users')->where('email', 'like', '%@example.test')->delete();
    }
};
