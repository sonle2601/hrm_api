<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Employee;
use App\Models\WorkShift;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;

class WorkShiftController extends Controller
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
            $workShift = WorkShift::where('store_id', $storeId)->get();
    
            $formattedWorkShifts = $workShift->map(function ($shift) {
                return [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'start_time' => \Carbon\Carbon::parse($shift->start_time)->format('H:i'),
                    'end_time' => \Carbon\Carbon::parse($shift->end_time)->format('H:i'),
                    'status' => $shift->status,
                    'store_id' => $shift->store_id,
                    'created_at' => $shift->created_at,
                    'updated_at' => $shift->updated_at,
                ];
            });
        
            return response()->json($formattedWorkShifts, 200);
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
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => true,
                'store_id' => $storeId,
            ];
    
            WorkShift::create($data);

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

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');
    
        $employee = Employee::with('store')->where('user_id', $userId)->first();

        if ($employee) {
            $storeId = $employee->store->id;
            $workShift = WorkShift::where('store_id', $storeId)->get();
    
            $formattedWorkShifts = $workShift->map(function ($shift) {
                return [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'start_time' => \Carbon\Carbon::parse($shift->start_time)->format('H:i'),
                    'end_time' => \Carbon\Carbon::parse($shift->end_time)->format('H:i'),
                    'status' => $shift->status,
                    'store_id' => $shift->store_id,
                    'created_at' => $shift->created_at,
                    'updated_at' => $shift->updated_at,
                ];
            });
        
            return response()->json($formattedWorkShifts, 200);
        } else {
            return response()->json([
                'message' => 'Error: Store not found workShift',
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $token = $request->bearerToken();
        $manage_id = JWTAuth::getPayload($token)->get('sub');
    
        $store = Store::where('manager_id', $manage_id)->first();
    
        if ($store) {
            $storeId = $store->id;
            $workShiftData = [
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => $request->status,
                'store_id' => $storeId,
            ];
    
            if ($request->has('id')) {
                $workShift = WorkShift::find($request->id);
                if ($workShift) {
                    // Chỉ cập nhật các trường có dữ liệu mới được gửi
                    $workShift->update($workShiftData);
    
                    return response()->json([
                        'message' => 'Update successful workShift',
                        'data' => $workShiftData
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'WorkShift not found',
                    ], 404);
                }
            } else {
                $workShift = WorkShift::create($workShiftData);
                return response()->json([
                    'message' => 'Create a successful workShift',
                    'data' => $workShiftData,
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
