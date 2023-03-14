<?php

namespace App\Repositories\Eloquent\GatewayApplication;

use App\Http\Controllers\BaseController;
use App\Models\Application;
use App\Repositories\Interfaces\GatewayApplication\GatewayApplicationRepositoryInterface;
use App\Services\MessageGatewayService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GatewayApplicationRepository implements GatewayApplicationRepositoryInterface
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
        return $this->apiController->trueResult("Semua data aplikasi berhasil di ambil", Application::all());
    }

    public function getById($id)
    {
        return $this->apiController->trueResult("Detail data berhasil di ambil", Application::find($id));
    }

    public function create($request)
    {
        $input = $request->all();
        return $this->apiController->trueResult("Data applikasi berhasil di tambahkan", Application::create($input));
    }

    public function update($request, $id)
    {
        $application = Application::find($id);

        if ($application) {
            $application->name = $request->name;
            $application->description = $request->description;
            $application->base_url = $request->base_url;
            $application->image_url = $request->image_url;
            $application->slug = Str::slug($request->name);

            if ($application->isClean()) {
                return $this->apiController->falseResult('Tidak ada perubahan data yang anda masukan', null);
            }

            $application->save();

            return $this->apiController->trueResult("Data applikasi berhasil di update", $application);
        } else {
            return $this->apiController->falseResult("Data applikasi gagal di update", null);
        }
    }

    public function delete($id)
    {
        $application = Application::find($id);

        if ($application) {
            Schema::dropIfExists($application->slug);
            $application->delete();
            return $this->apiController->trueResult("Data applikasi berhasil di hapus", $application);
        } else {
            return $this->apiController->falseResult("Data applikasi gagal di hapus", null);
        }
    }
}
