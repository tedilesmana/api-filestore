<?php

namespace App\Http\Controllers\Api\Hris;

use App\Http\Controllers\BaseController;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $response = Setting::create($request->all());
            return $this->successResponse("Created data setting", $response);
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $response = Setting::where("type", $id)->get();
            return $this->successResponse("All data setting", $response);
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $detail = Setting::find($id);
            $detail->type = $request->type;
            $detail->value = $request->value;
            $detail->save();
            return $this->successResponse("Updated data setting", true);
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $response = Setting::where('id', $id)->delete();
            return $this->successResponse("Deleted data setting", $response);
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }
}
