<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCommentsSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comments:update-schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update comments table schema to use content column';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (Schema::hasColumn('comments', 'comment')) {
            DB::statement('ALTER TABLE comments CHANGE `comment` content TEXT NOT NULL');
            $this->info('Comments table updated successfully - renamed comment to content.');
        } else {
            $this->info('Comments table already has content column.');
        }

        return 0;
    }
}
