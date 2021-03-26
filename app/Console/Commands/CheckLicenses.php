<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\License;
use Illuminate\Support\Facades\Date;

class CheckLicenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check_licenses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is a custom task that checks user licenses everyday for the ones that have expired and changes the status to reflect that';

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
     * @return int
     */
    public function handle()
    {
        try {
            $licenses = License::where('expires_at', '<', date("Y-m-d H:i:s"));
            foreach ($licenses as $license) {
                $license->status = 0;
                $license->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        error_log($th->getMessage());
        }
    }
}
