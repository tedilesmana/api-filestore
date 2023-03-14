<?php

namespace App\Repositories\Eloquent\GatewayManager;

use App\Http\Controllers\BaseController;
use App\Models\Application;
use App\Models\Feature;
use App\Models\Module;
use App\Repositories\Interfaces\GatewayManager\GatewayManagerRepositoryInterface;
use App\Services\MessageGatewayService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GatewayManagerRepository implements GatewayManagerRepositoryInterface
{
    protected $apiController;
    protected $messageGatewayService;

    public function __construct(BaseController $apiController, MessageGatewayService $messageGatewayService)
    {
        $this->apiController = $apiController;
        $this->messageGatewayService = $messageGatewayService;
    }

    public function create($request)
    {
        $application = Application::find($request->application_id);
        $module = Module::find($request->module_id);
        $feature = Feature::find($request->feature_id);

        if (is_null($feature)) {
            return $this->apiController->falseResult("Fitur tidak di temukan", null);
        }

        if (is_null($application)) {
            return $this->apiController->falseResult("Aplikasi tidak di temukan", null);
        }

        if (is_null($module)) {
            return $this->apiController->falseResult("Modul tidak di temukan", null);
        }

        if (!is_null($feature) && !is_null($application) && !is_null($module)) {
            if (!Schema::hasTable($application->slug)) {
                Schema::connection('mysql')->create($application->slug, function (Blueprint $table) {
                    $table->increments('id');
                    $table->foreignId('application_id')->constrained()
                        ->onUpdate('restrict')
                        ->onDelete('restrict');
                    $table->foreignId('module_id')->constrained()
                        ->onUpdate('restrict')
                        ->onDelete('restrict');
                    $table->foreignId('feature_id')->constrained()
                        ->onUpdate('restrict')
                        ->onDelete('restrict');
                    $table->string('name')->unique();
                    $table->string('slug');
                    $table->string('description');
                    $table->string('link_api_application');
                    $table->string('link_api_gateway');
                    $table->string('methode');
                    $table->string('payload')->nullable();
                    $table->timestamps();
                });
            }

            $base_url = env('APP_URL');
            $input = $request->all();
            $input["slug"] = Str::slug($request->name);
            $input["link_api_gateway"] = "{$base_url}/api/gateway-manager/{$application->slug}/{$module->slug}/{$feature->slug}/{$input["slug"]}";

            $result = DB::table($application->slug)->insert(
                $input
            );
            return $this->apiController->trueResult("Data request berhasil di buat", $result);
        }
    }

    public function updateRequest($app, $module, $feature, $title, $action, $request)
    {
        $applicationItem = DB::table("applications")->where("slug", $app)->first();
        $moduleItem = DB::table("modules")->where("slug", $module)->first();
        $featureItem = DB::table("applications")->where("slug", $feature)->first();

        if ($action == "detail") {
            $requestItem = DB::table($app)->where("slug", $title)->first();
            return $this->apiController->trueResult("Detail request berhasil di ambil", $requestItem);
        }

        if ($action == "by_application_id") {
            $listByAppId = DB::table($app)->where("application_id", $applicationItem->id)->get();
            return $this->apiController->trueResult("Data request by applikasi berhasil di ambil", $listByAppId);
        }

        if ($action == "by_module_id") {
            $listByModuleId = DB::table($app)->where("module_id", $moduleItem->id)->get();
            return $this->apiController->trueResult("Data request by module berhasil di ambil", $listByModuleId);
        }

        if ($action == "by_feature_id") {
            $listByFeatureId = DB::table($app)->where("feature_id", $featureItem->id)->get();
            return $this->apiController->trueResult("Detail request by feature berhasil di ambil", $listByFeatureId);
        }

        if ($action == "delete") {
            $deleteItem = DB::table($app)->where("slug", $title)->delete();
            return $this->apiController->trueResult("Delete request berhasil di lakukan", $deleteItem);
        }

        if ($action == "update") {
            $updateItem = DB::table($app)->where("slug", $title)->update($request->all());
            return $this->apiController->trueResult("Update request berhasil di lakukan", $updateItem);
        }
    }

    public function proceedRequest($app, $module, $feature, $title)
    {
        $applicationItem = DB::table("applications")->where("slug", $app)->first();
        $requestItem = DB::table($app)->where("slug", $title)->first();
        $requestItem->token = $applicationItem->token;
        return $this->apiController->trueResult("Detail request berhasil di ambil", $requestItem);
    }
}