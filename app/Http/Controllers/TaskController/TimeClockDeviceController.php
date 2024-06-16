<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\TimeClockDevice;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;

class TimeClockDeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $token = $request->bearerToken();
        $manage_id = JWTAuth::getPayload($token)->get('sub');
    
        $store = Store::where('manager_id', $manage_id)->first();
    
        if ($store) {
            $storeId = $store->id;
            $timeClockDevices = TimeClockDevice::where('store_id', $storeId)->first();
    
            return response()->json(
                    $timeClockDevices
            , 200);
        } else {
            return response()->json([
                'message' => 'Error: Store not found',
            ], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $token = $request->bearerToken();
    
        $manage_id = JWTAuth::getPayload($token)->get('sub');

        $store = Store::where('manager_id', $manage_id)->first();

        if ($store) {
            $storeId = $store->id;
            $data = [
                'device_name' => $request->device_name,
                'mac_address' => $request->mac_address,
                'store_id' => $storeId,
            ];
    
            TimeClockDevice::create($data);

            return response()->json(
                [
                    'message' => 'Create a successfull',
                    'data'=>$request->all(),
            ], 
                201
            );

        } else {
            return response()->json(
                [
                    'message' => 'Error',
            ], 
                404
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    
  
    public function update(Request $request)
    {
        $token = $request->bearerToken();
        $manage_id = JWTAuth::getPayload($token)->get('sub');
    
        $store = Store::where('manager_id', $manage_id)->first();
    
        if ($store) {
            $storeId = $store->id;
            $timeClockDeviceData = [
                'device_name' =>$request->device_name,
                'mac_address' => $request->mac_address,
                'store_id' => $storeId,
            ];
    
            if ($request->has('id')) {
                $timeClockDevice = TimeClockDevice::find($request->id);
                if ($timeClockDevice) {
                    $timeClockDevice->update($timeClockDeviceData);
    
                    return response()->json([
                        'message' => 'Update successful TimeClockDevice',
                        'data' => $timeClockDevice
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'TimeClockDevice not found',
                    ], 404);
                }
            } else {
                $timeClockDevice = timeClockDevice::create($timeClockDeviceData);
                return response()->json([
                    'message' => 'Create a successful timeClockDevice',
                    'data' => $timeClockDeviceData,
                ], 201);
            }
        } else {
            return response()->json([
                'message' => 'Error: Store not found',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
