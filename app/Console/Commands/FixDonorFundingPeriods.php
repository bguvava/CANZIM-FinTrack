<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDonorFundingPeriods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'donors:fix-funding-periods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update project_donors records with null funding periods to use project dates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fixing funding periods for project-donor relationships...');

        // Get all project-donor relationships with null funding periods
        $relationships = DB::table('project_donors')
            ->join('projects', 'project_donors.project_id', '=', 'projects.id')
            ->select(
                'project_donors.donor_id',
                'project_donors.project_id',
                'project_donors.funding_period_start',
                'project_donors.funding_period_end',
                'projects.start_date',
                'projects.end_date'
            )
            ->where(function ($query) {
                $query->whereNull('project_donors.funding_period_start')
                    ->orWhereNull('project_donors.funding_period_end');
            })
            ->get();

        $updated = 0;
        foreach ($relationships as $rel) {
            DB::table('project_donors')
                ->where('donor_id', $rel->donor_id)
                ->where('project_id', $rel->project_id)
                ->update([
                    'funding_period_start' => $rel->funding_period_start ?? $rel->start_date,
                    'funding_period_end' => $rel->funding_period_end ?? $rel->end_date,
                ]);
            $updated++;
        }

        $this->info("✓ Updated {$updated} project-donor relationships with funding periods from project dates");

        return Command::SUCCESS;
    }
}
