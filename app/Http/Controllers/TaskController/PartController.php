<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Part;
use App\Models\Store;
use Tymon\JWTAuth\Facades\JWTAuth;

class PartController extends Controller
{
    public function part(Request $request)  {

        $token = $request->bearerToken();
    
        $manage_id = JWTAuth::getPayload($token)->get('sub');

        $store = Store::where('manager_id', $manage_id)->first();

        if ($store) {
            $storeId = $store->id;
            $partData =  [
            'store_id' => $storeId,
            'part_name' => $request->part_name,
            'part_description' => $request->part_description,
            'part_status' => 1,
            ];

            $part = Part::create($partData);

            return response()->json(
                [
                    'message' => 'Create a successful parts',
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

    public function getPart(Request $request)  
    {
        $token = $request->bearerToken();
        $manage_id = JWTAuth::getPayload($token)->get('sub');
    
        $store = Store::where('manager_id', $manage_id)->first();
    
        if ($store) {
            $storeId = $store->id;
            $parts = Part::where('store_id', $storeId)
            ->get();
    
            if($parts){
                return response()->json(
                    $parts
            , 200);
            }else{
                return  response()->json(
                    $parts
            , 200);
            }
        } else {
            return response()->json([
                'message' => 'Error: Store not found',
            ], 404);
        }
    }

    public function updatePart(Request $request)
    {
        $token = $request->bearerToken();
        $manage_id = JWTAuth::getPayload($token)->get('sub');
    
        $store = Store::where('manager_id', $manage_id)->first();
    
        if ($store) {
            $storeId = $store->id;
            $partData = [
                'store_id' => $storeId,
                'part_name' => $request->part_name,
                'part_description' => $request->part_description,
                'part_status' => $request->part_status,
            ];
    
            if ($request->has('id')) {
                $part = Part::find($request->id);
                if ($part) {
                    // Chỉ cập nhật các trường có dữ liệu mới được gửi
                    $part->fill($partData)->save();
                    
                    return response()->json([
                        'message' => 'Update successful part',
                        'data' => $partData
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Part not found',
                    ], 404);
                }
            } else {
                // Nếu không có ID, tạo mới bản ghi
                $part = Part::create($partData);
                return response()->json([
                    'message' => 'Create a successful part',
                    'data' => $partData,
                ], 201);
            }
        } else {
            return response()->json([
                'message' => 'Error',
            ], 404);
        }
    }
    
    
    
}
