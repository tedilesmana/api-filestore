<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\SeederDb\SeederDbRepositoryInterface;
use App\Models\LogSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeederEmployeeCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seederEmployee:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $fromRepository;
    public function __construct(SeederDbRepositoryInterface $fromRepository)
    {
        parent::__construct();
        $this->fromRepository = $fromRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $logSeeder = LogSeeder::where('name', 'employees')->first();
        try {
            if ($logSeeder) {
                DB::beginTransaction();
                $response = $this->fromRepository->insertEmployees($logSeeder->current_page + 1);
                if ($response->success) {
                    $log_seeder = [];
                    $log_seeder['name'] = 'employees';
                    $log_seeder['current_page'] = $logSeeder->current_page + 1;

                    LogSeeder::updateOrCreate([
                        'name'   => 'employees',
                    ], $log_seeder);

                    DB::commit();
                } else {
                    DB::rollBack();
                }
                //. "jumlah data di proses : " . $response->data 
                Log::info("Seeder employees success page: " . $logSeeder->current_page + 1 . " - jumlah data di proses : " . $response->data);
            } else {
                DB::beginTransaction();
                $response = $this->fromRepository->insertEmployees(1);
                if ($response->success) {
                    $log_seeder = [];
                    $log_seeder['name'] = 'employees';
                    $log_seeder['current_page'] = 1;

                    LogSeeder::updateOrCreate([
                        'name'   => 'employees',
                    ], $log_seeder);

                    DB::commit();
                } else {
                    DB::rollBack();
                }
                Log::info("Seeder employees success page: " . "1" . " - jumlah data di proses : " . $response->data);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Seeder employees failed page: " . $th);
            if ($logSeeder) {
                Log::error("Seeder employees failed page: " . $logSeeder->current_page);
            } else {
                Log::error("Seeder employees failed page: " . '1');
            }
        }
    }
}
