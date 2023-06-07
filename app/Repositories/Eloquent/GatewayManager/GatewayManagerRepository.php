<?php

namespace App\Repositories\Eloquent\GatewayManager;

use App\Http\Controllers\BaseController;
use App\Models\Application;
use App\Models\Feature;
use App\Models\Module;
use App\Repositories\Interfaces\GatewayManager\GatewayManagerRepositoryInterface;
use App\Services\MessageGatewayService;
use Carbon\Carbon;
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
            $input["payload"] = json_encode($request->payload);
            $input["created_at"] = Carbon::now();
            $input["updated_at"] = Carbon::now();
            $listIds = $request->ids ?? [];
            $ids = '';
            for ($i = 0; $i < count($listIds); $i++) {
                $ids = $ids . '/' . $listIds[$i];
            }
            $input["link_api_gateway"] = "{$base_url}/api/gateway-manager/{$application->slug}/{$module->slug}/{$feature->slug}/{$input["slug"]}" . $ids;
            unset($input["ids"]);
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
        $featureItem = DB::table("features")->where("slug", $feature)->first();

        if ($action == "detail") {
            $requestItem = DB::table($app)
                ->leftJoin('applications', function ($join)  use ($app) {
                    $join->on('applications.id', '=', $app . '.application_id');
                })
                ->leftJoin('modules', function ($join)  use ($app) {
                    $join->on('modules.id', '=', $app . '.module_id');
                })
                ->leftJoin('features', function ($join)  use ($app) {
                    $join->on('features.id', '=', $app . '.feature_id');
                })
                ->where($app . ".slug", $title)
                ->select($app . '.*', 'applications.name as name_application', 'modules.name as name_module', 'features.name as name_feature')
                ->first();
            return $this->apiController->trueResult("Detail request berhasil di ambil", $requestItem);
        }

        if ($action == "by_application_id") {
            $listByAppId = DB::table($app)
                ->leftJoin('applications', function ($join)  use ($app) {
                    $join->on('applications.id', '=', $app . '.application_id');
                })
                ->leftJoin('modules', function ($join)  use ($app) {
                    $join->on('modules.id', '=', $app . '.module_id');
                })
                ->leftJoin('features', function ($join)  use ($app) {
                    $join->on('features.id', '=', $app . '.feature_id');
                })
                ->where("applications.id", $applicationItem->id)
                ->select($app . '.*', 'applications.name as name_application', 'modules.name as name_module', 'features.name as name_feature')
                ->get();
            return $this->apiController->trueResult("Data request by applikasi berhasil di ambil", $listByAppId);
        }

        if ($action == "by_module_id") {
            $listByModuleId = DB::table($app)
                ->leftJoin('applications', function ($join)  use ($app) {
                    $join->on('applications.id', '=', $app . '.application_id');
                })
                ->leftJoin('modules', function ($join)  use ($app) {
                    $join->on('modules.id', '=', $app . '.module_id');
                })
                ->leftJoin('features', function ($join)  use ($app) {
                    $join->on('features.id', '=', $app . '.feature_id');
                })
                ->where("modules.id", $moduleItem->id)
                ->select($app . '.*', 'applications.name as name_application', 'modules.name as name_module', 'features.name as name_feature')
                ->get();
            return $this->apiController->trueResult("Data request by module berhasil di ambil", $listByModuleId);
        }

        if ($action == "by_feature_id") {
            $listByFeatureId = DB::table($app)
                ->leftJoin('applications', function ($join)  use ($app) {
                    $join->on('applications.id', '=', $app . '.application_id');
                })
                ->leftJoin('modules', function ($join)  use ($app) {
                    $join->on('modules.id', '=', $app . '.module_id');
                })
                ->leftJoin('features', function ($join)  use ($app) {
                    $join->on('features.id', '=', $app . '.feature_id');
                })
                ->where("features.id", $featureItem->id)
                ->select($app . '.*', 'applications.name as name_application', 'modules.name as name_module', 'features.name as name_feature')
                ->get();
            return $this->apiController->trueResult("Detail request by feature berhasil di ambil", $listByFeatureId);
        }

        if ($action == "delete") {
            $deleteItem = DB::table($app)->where("slug", $title)->delete();
            return $this->apiController->trueResult("Delete request berhasil di lakukan", $deleteItem);
        }

        if ($action == "update") {
            $base_url = env('APP_URL');
            $input = $request->all();
            $input["slug"] = Str::slug($request->name);
            $listIds = $request->ids ?? [];
            $ids = '';
            for ($i = 0; $i < count($listIds); $i++) {
                $ids = $ids . '/' . $listIds[$i];
            }
            $input["link_api_gateway"] = "{$base_url}/api/gateway-manager/{$applicationItem->slug}/{$moduleItem->slug}/{$featureItem->slug}/{$input["slug"]}" . $ids;
            $input["payload"] = json_encode($request->payload);
            $input["updated_at"] = Carbon::now();
            unset($input["ids"]);

            $updateItem = DB::table($app)->where("slug", $title)->update($input);
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
