<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Employee;
use App\Models\Store;
use App\Models\SalaryPenalty;

class SalaryPenaltyController extends Controller
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
            $salaryPenalty = SalaryPenalty::where('store_id', $storeId)
            ->with('employee.information')
            ->orderBy('created_at', 'desc')
            ->get();
    
            return response()->json(
                    $salaryPenalty
            , 200);
        } else {
            return response()->json([
                'message' => 'Error: Store not found',
            ], 404);
        }
    }

    public function showSalaryPenaltyEmployee(Request $request,String $employeeId,string $startDate,String $endDate )
    {
        $token = $request->bearerToken();
        $manage_id = JWTAuth::getPayload($token)->get('sub');
    
        $store = Store::where('manager_id', $manage_id)->first();

        if ($store) {
            $storeId = $store->id;
            $salaryPenalty = SalaryPenalty::where('store_id', $storeId)
            ->where('employee_id', $employeeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('employee.information')
            ->get();
    
            return response()->json(
                    $salaryPenalty
            , 200);
        } else {
            return response()->json([
                'message' => 'Error: Store not found',
            ], 404);
        }
    }

    public function showSalaryPenaltyPersonal(Request $request,string $startDate,String $endDate )
    {
        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');
        $employee = Employee::where('user_id', $userId)->firstOrFail();
        $employeeId = $employee->id;

            $salaryPenalty = SalaryPenalty::where('employee_id', $employeeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('employee.information')
            ->get();
    
            return response()->json(
                    $salaryPenalty
            , 200);
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

        if($store){
            $storeId = $store->id;
            $data = [
                'employee_id' => $request->employee_id,
                'reason' => $request->reason,
                'amount' => $request->amount,
                'store_id' => $storeId,
            ];
            SalaryPenalty::create($data);

            return response()->json(
                [
                    'message' => 'Create a successful salaryBonus',
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
