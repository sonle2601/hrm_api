<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\TimeClockDevice;
use Tymon\JWTAuth\Facades\JWTAuth;


class StoreController extends Controller
{
    public function store(Request $request)  {

        $token = $request->bearerToken();
    
        $user_id = JWTAuth::getPayload($token)->get('sub');
        $email = JWTAuth::getPayload($token)->get('email');


    
        // Tiếp tục thêm thông tin với user_id được trích xuất từ JWT
        $storeData = [
            'manager_id' => $user_id,
            'email' => $email,
            'ten' => $request->ten,
            'so_dien_thoai' => $request->so_dien_thoai,
            'dia_chi'=>$request->dia_chi,
        ];

        $store = Store::create($storeData);

        $data = [
            'device_name' => $request->ten,
            'mac_address' => $request->so_dien_thoai,
            'store_id' => $store->id,
        ];

        TimeClockDevice::create($data);
    
        return response()->json(
            [
                'message' => 'Create a successful store',
                'data'=>$request->all(),
        ], 
            201
        );
    }

    public function checkStore(Request $request)
    {
        $token = $request->bearerToken();
    
        $manager_id = JWTAuth::getPayload($token)->get('sub');

        $storeExists = Store::where('manager_id', $manager_id)->exists();

        return response()->json(['store_exists' => $storeExists]);
    }

    public function getStoreByManagerId(Request $request)
    {
      
        $token = $request->bearerToken();
    
        $managerId = JWTAuth::getPayload($token)->get('sub');

        $store = Store::where('manager_id', $managerId)->first();

        if (!$store) {
            return response()->json(['message' => 'Cửa hàng không tồn tại'], 404);
        }

        return response()->json($store, 200);
    }


    public function update(Request $request)
    {
        $store = Store::find($request->id);
            if ($store) {
                $storeData = [
                    'ten' => $request->ten,
                    'so_dien_thoai' => $request->so_dien_thoai,
                    'dia_chi'=>$request->dia_chi,
                ];
                $store->update($storeData);
                    return response()->json([
                        'message' => 'Update successful store',
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Store not found',
                    ], 404);
                }
    }


    
}
