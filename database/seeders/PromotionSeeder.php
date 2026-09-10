<?php

namespace Database\Seeders;

use App\Models\Personnel;
use App\Models\Promotions;
use App\Models\Ranks;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => '12345678',
            'role' => 'admin',
        ]);

        $personnels = Personnel::with('rank')->inRandomOrder()->take(4)->get();
        $ranks = Ranks::orderBy('level')->get();

        if ($personnels->isEmpty() || $ranks->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        $samplePromotions = [
            ['status' => 'pending', 'personnel_index' => 0, 'days_ahead' => 5],
            ['status' => 'approved', 'personnel_index' => 1, 'days_ago' => 20],
            ['status' => 'pending', 'personnel_index' => 2, 'days_ahead' => 12],
            ['status' => 'approved', 'personnel_index' => 3, 'days_ago' => 60],
        ];

        foreach ($samplePromotions as $index => $promo) {
            $personnel = $personnels[$promo['personnel_index']] ?? null;
            if (! $personnel) {
                continue;
            }

            $currentRank = $personnel->rank;
            if (! $currentRank) {
                continue;
            }

            $nextRank = $ranks->first(fn ($r) => $r->level > $currentRank->level);

            if (! $nextRank) {
                continue;
            }

            $promotion_date = isset($promo['days_ago'])
                ? $now->copy()->subDays($promo['days_ago'])->toDateString()
                : $now->copy()->addDays($promo['days_ahead'])->toDateString();

            $data = [
                'personnel_id' => $personnel->personnel_id,
                'from_rank_id' => $currentRank->rank_id,
                'to_rank_id' => $nextRank->rank_id,
                'promotion_date' => $promotion_date,
                'status' => $promo['status'],
                'remarks' => 'Recommended for promotion based on performance and years of service.',
                'recommendation' => 'Strong leadership and service record warrant promotion.',
                'recommended_by' => $admin->id,
            ];

            if ($promo['status'] === 'approved') {
                $data['approved_by'] = $admin->id;
                $data['approved_at'] = $now->copy()->subDays(3);
            }

            Promotions::create($data);
        }
    }
}
