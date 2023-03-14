<?php

namespace App\Repositories\Eloquent\GatewayFeature;

use App\Http\Controllers\BaseController;
use App\Models\Feature;
use App\Repositories\Interfaces\GatewayFeature\GatewayFeatureRepositoryInterface;
use App\Services\MessageGatewayService;
use Illuminate\Support\Str;

class GatewayFeatureRepository implements GatewayFeatureRepositoryInterface
{
    protected $apiController;
    protected $messageGatewayService;

    public function __construct(BaseController $apiController, MessageGatewayService $messageGatewayService)
    {
        $this->apiController = $apiController;
        $this->messageGatewayService = $messageGatewayService;
    }

    public function getAll()
    {
        return $this->apiController->trueResult("Semua data berhasil di ambil", Feature::all());
    }

    public function getById($id)
    {
        return $this->apiController->trueResult("Semua data dengan id yang di maksud berhasil di ambil", Feature::where("module_id", $id)->get());
    }

    public function create($request)
    {
        $input = $request->all();
        return $this->apiController->trueResult("Data feature berhasil di tambahkan", Feature::create($input));
    }

    public function update($request, $id)
    {
        $application = Feature::find($id);

        if ($application) {
            $application->name = $request->name;
            $application->description = $request->description;
            $application->module_id = $request->module_id;
            $application->slug = Str::slug($request->name);

            if ($application->isClean()) {
                return $this->apiController->falseResult('Tidak ada perubahan data yang anda masukan', null);
            }

            $application->save();

            return $this->apiController->trueResult("Data feature berhasil di update", $application);
        } else {
            return $this->apiController->falseResult("Data feature gagal di update", null);
        }
    }

    public function delete($id)
    {
        $application = Feature::find($id);

        if ($application) {
            $application->delete();

            return $this->apiController->trueResult("Data feature berhasil di hapus", $application);
        } else {
            return $this->apiController->falseResult("Data feature gagal di hapus", null);
        }
    }
}
