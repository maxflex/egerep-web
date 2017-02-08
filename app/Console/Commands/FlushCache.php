<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FlushCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:flush {key?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'flush redis cache';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($key = $this->argument('key')) {
            \Cache::forget($key);
        } else {
            \Cache::flush();
        }
    }
}
