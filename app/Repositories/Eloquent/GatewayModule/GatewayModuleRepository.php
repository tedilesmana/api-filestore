<?php

namespace App\Repositories\Eloquent\GatewayModule;

use App\Http\Controllers\BaseController;
use App\Models\Module;
use App\Repositories\Interfaces\GatewayModule\GatewayModuleRepositoryInterface;
use App\Services\MessageGatewayService;
use Illuminate\Support\Str;

class GatewayModuleRepository implements GatewayModuleRepositoryInterface
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
        return $this->apiController->trueResult("Semua data berhasil di ambil", Module::all());
    }

    public function getById($id)
    {
        return $this->apiController->trueResult("Semua data dengan id yang di maksud berhasil di ambil", Module::where("application_id", $id)->get());
    }

    public function create($request)
    {
        $input = $request->all();
        return $this->apiController->trueResult("Data modul berhasil di tambahkan", Module::create($input));
    }

    public function update($request, $id)
    {
        $application = Module::find($id);

        if ($application) {
            $application->name = $request->name;
            $application->description = $request->description;
            $application->application_id = $request->application_id;
            $application->slug = Str::slug($request->name);

            if ($application->isClean()) {
                return $this->apiController->falseResult('Tidak ada perubahan data yang anda masukan', null);
            }

            $application->save();

            return $this->apiController->trueResult("Data modul berhasil di update", $application);
        } else {
            return $this->apiController->falseResult("Data modul gagal di update", null);
        }
    }

    public function delete($id)
    {
        $application = Module::find($id);

        if ($application) {
            $application->delete();

            return $this->apiController->trueResult("Data modul berhasil di hapus", $application);
        } else {
            return $this->apiController->falseResult("Data modul gagal di hapus", null);
        }
    }
}
