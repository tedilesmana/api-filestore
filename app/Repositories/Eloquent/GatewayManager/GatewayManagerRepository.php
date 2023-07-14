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
            if (!Schema::hasTable('app-' . $application->slug)) {
                Schema::connection('mysql')->create('app-' . $application->slug, function (Blueprint $table) {
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
                    $table->bigInteger('sort')->default(0);
                    $table->string('name')->unique();
                    $table->string('slug');
                    $table->string('description');
                    $table->string('link_api_application');
                    $table->string('methode');
                    $table->longText('ids')->nullable();
                    $table->longText('body')->nullable();
                    $table->longText('params')->nullable();
                    $table->longText('headers')->nullable();
                    $table->longText('authorization')->nullable();
                    $table->timestamps();
                });
            }

            $input = $request->all();
            $input["slug"] = Str::slug($request->name);
            $input["body"] = $request->body;
            $input["params"] = $request->params;
            unset($input["data_headers"]);
            $input["headers"] = $request->data_headers;
            $input["authorization"] = $request->authorization;
            $input["ids"] = $request->ids;
            $input["created_at"] = Carbon::now();
            $input["updated_at"] = Carbon::now();
            $result = DB::table('app-' . $application->slug)->insert(
                $input
            );
            return $this->apiController->trueResult("Data request berhasil di buat", $result);
        }
    }

    public function managementRequest($app, $module, $feature, $title, $action, $request)
    {
        $applicationItem = DB::table("applications")->where("slug", $app)->first();
        $moduleItem = DB::table("modules")->where("slug", $module)->first();
        $featureItem = DB::table("features")->where("slug", $feature)->first();

        if ($action == "detail") {
            $requestItem = DB::table('app-' . $app)
                ->leftJoin('applications', function ($join)  use ($app) {
                    $join->on('applications.id', '=', 'app-' . $app . '.application_id');
                })
                ->leftJoin('modules', function ($join)  use ($app) {
                    $join->on('modules.id', '=', 'app-' . $app . '.module_id');
                })
                ->leftJoin('features', function ($join)  use ($app) {
                    $join->on('features.id', '=', 'app-' . $app . '.feature_id');
                })
                ->where('app-' . $app . ".slug", $title)
                ->select('app-' . $app . '.*', 'applications.name as name_application', 'modules.name as name_module', 'features.name as name_feature')
                ->first();
            return $this->apiController->trueResult("Detail request berhasil di ambil", $requestItem);
        }

        if ($action == "by_application_id") {
            $listByAppId = DB::table('app-' . $app)
                ->leftJoin('applications', function ($join)  use ($app) {
                    $join->on('applications.id', '=', 'app-' . $app . '.application_id');
                })
                ->leftJoin('modules', function ($join)  use ($app) {
                    $join->on('modules.id', '=', 'app-' . $app . '.module_id');
                })
                ->leftJoin('features', function ($join)  use ($app) {
                    $join->on('features.id', '=', 'app-' . $app . '.feature_id');
                })
                ->where("applications.id", $applicationItem->id)
                ->select('app-' . $app . '.*', 'applications.name as name_application', 'modules.name as name_module', 'features.name as name_feature')
                ->get();
            return $this->apiController->trueResult("Data request by applikasi berhasil di ambil", $listByAppId);
        }

        if ($action == "by_module_id") {
            $listByModuleId = DB::table('app-' . $app)
                ->leftJoin('applications', function ($join)  use ($app) {
                    $join->on('applications.id', '=', 'app-' . $app . '.application_id');
                })
                ->leftJoin('modules', function ($join)  use ($app) {
                    $join->on('modules.id', '=', 'app-' . $app . '.module_id');
                })
                ->leftJoin('features', function ($join)  use ($app) {
                    $join->on('features.id', '=', 'app-' . $app . '.feature_id');
                })
                ->where("modules.id", $moduleItem->id)
                ->select('app-' . $app . '.*', 'applications.name as name_application', 'modules.name as name_module', 'features.name as name_feature')
                ->get();
            return $this->apiController->trueResult("Data request by module berhasil di ambil", $listByModuleId);
        }

        if ($action == "by_feature_id") {
            $listByFeatureId = DB::table('app-' . $app)
                ->leftJoin('applications', function ($join)  use ($app) {
                    $join->on('applications.id', '=', 'app-' . $app . '.application_id');
                })
                ->leftJoin('modules', function ($join)  use ($app) {
                    $join->on('modules.id', '=', 'app-' . $app . '.module_id');
                })
                ->leftJoin('features', function ($join)  use ($app) {
                    $join->on('features.id', '=', 'app-' . $app . '.feature_id');
                })
                ->where("features.id", $featureItem->id)
                ->select('app-' . $app . '.*', 'applications.name as name_application', 'modules.name as name_module', 'features.name as name_feature')
                ->get();
            return $this->apiController->trueResult("Detail request by feature berhasil di ambil", $listByFeatureId);
        }

        if ($action == "delete") {
            $deleteItem = DB::table('app-' . $app)->where("slug", $title)->delete();
            return $this->apiController->trueResult("Delete request berhasil di lakukan", $deleteItem);
        }

        if ($action == "update") {
            $input = $request->all();
            $input["slug"] = Str::slug($request->name);
            $input["body"] = $request->body;
            $input["params"] = $request->params;
            unset($input["data_headers"]);
            $input["headers"] = $request->data_headers;
            $input["authorization"] = $request->authorization;
            $input["ids"] = $request->ids;
            $input["updated_at"] = Carbon::now();

            $updateItem = DB::table('app-' . $app)->where("slug", $title)->update($input);
            return $this->apiController->trueResult("Update request berhasil di lakukan", $updateItem);
        }
    }

    public function proceedRequest($app, $module, $feature, $title)
    {
        $applicationItem = DB::table("applications")->where("slug", $app)->first();
        $requestItem = DB::table('app-' . $app)->where("slug", $title)->first();
        $requestItem->token = $applicationItem->token;
        return $this->apiController->trueResult("Detail request berhasil di ambil", $requestItem);
    }
}
