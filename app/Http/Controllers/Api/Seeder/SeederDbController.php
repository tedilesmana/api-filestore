<?php

namespace App\Http\Controllers\Api\Seeder;

use App\Http\Controllers\BaseController;
use App\Repositories\Interfaces\SeederDb\SeederDbRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SeederDbController extends BaseController
{
    protected $fromRepository;
    public function __construct(SeederDbRepositoryInterface $fromRepository)
    {
        $this->fromRepository = $fromRepository;
    }

    public function insertMahasiswa(Request $request)
    {
        try {
            DB::beginTransaction();
            $response = $this->fromRepository->insertMahasiswa($request->page);
            if ($response->success) {
                DB::commit();
                return $this->successResponse('Anda telah berhasil seeder', $response->data);
            } else {
                DB::rollBack();
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->badResponse($e->getMessage(), $e);
        }
    }

    public function insertDlbEmployees(Request $request)
    {
        try {
            DB::beginTransaction();
            $response = $this->fromRepository->insertDlbEmployees($request->page);
            if ($response->success) {
                DB::commit();
                return $this->successResponse('Anda telah berhasil seeder', $response->data);
            } else {
                DB::rollBack();
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->badResponse($e->getMessage(), $e);
        }
    }

    public function insertEmployees(Request $request)
    {
        try {
            DB::beginTransaction();
            $response = $this->fromRepository->insertEmployees($request->page);
            if ($response->success) {
                DB::commit();
                return $this->successResponse('Anda telah berhasil seeder', $response->data);
            } else {
                DB::rollBack();
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->badResponse($e->getMessage(), $e);
        }
    }
}
