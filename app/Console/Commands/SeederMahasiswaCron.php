<?php

namespace App\Console\Commands;

use App\Models\LogSeeder;
use App\Repositories\Interfaces\SeederDb\SeederDbRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeederMahasiswaCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seederMahasiswa:cron';

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
        $logSeeder = LogSeeder::where('name', 'students')->first();
        try {
            if ($logSeeder) {
                DB::beginTransaction();
                $response = $this->fromRepository->insertMahasiswa($logSeeder->current_page + 1);
                if ($response->success) {
                    $log_seeder = [];
                    $log_seeder['name'] = 'students';
                    $log_seeder['jumlah_data'] = $response->data;
                    $log_seeder['current_page'] = $logSeeder->current_page + 1;

                    LogSeeder::updateOrCreate([
                        'name'   => 'students',
                    ], $log_seeder);

                    DB::commit();
                } else {
                    DB::rollBack();
                }
                Log::info("Seeder mahasiswa success page: " . $logSeeder->current_page + 1 . " - jumlah data di proses : " . $response->data);
            } else {
                DB::beginTransaction();
                $response = $this->fromRepository->insertMahasiswa(1);
                if ($response->success) {
                    $log_seeder = [];
                    $log_seeder['name'] = 'students';
                    $log_seeder['jumlah_data'] = $response->data;
                    $log_seeder['current_page'] = 1;

                    LogSeeder::updateOrCreate([
                        'name'   => 'students',
                    ], $log_seeder);

                    DB::commit();
                } else {
                    DB::rollBack();
                }
                Log::info("Seeder mahasiswa success page: " . "1" . " - jumlah data di proses : " . $response->data);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Seeder mahasiswa failed page: " . $th);
            if ($logSeeder) {
                Log::error("Seeder mahasiswa failed page: " . $logSeeder->current_page);
            } else {
                Log::error("Seeder mahasiswa failed page: " . '1');
            }
        }
    }
}
