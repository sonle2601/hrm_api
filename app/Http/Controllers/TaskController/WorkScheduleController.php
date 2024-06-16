<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Employee;
use App\Models\Part;
use App\Models\WorkShift;
use App\Models\Information;
use App\Models\WorkSchedule;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;
use DateTime;
use Carbon\Carbon;



class WorkScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            
            $workSchedules = $request->all(); // Lấy tất cả các bản ghi từ yêu cầu
    
           
            foreach ($workSchedules as $workSchedule) {
                $workShift = WorkShift::find($workSchedule['work_shift_id']);
            
                if (!$workShift) {
                    return response()->json([
                        'message' => 'Vui lòng chọn ca làm việc',
                    ], 404);
                }
    
                $currentDateTime = Carbon::now();
                $startTime = Carbon::parse($workShift->start_time);
    
                $workDateTime = Carbon::parse($workSchedule['date'])->setTimeFromTimeString($workShift->start_time);
                
                if ($currentDateTime->gt($workDateTime)) {
                    return response()->json([
                        'message' => 'Đã quá thời gian để bạn thêm lịch cho ca hiện tại',
                    ], 400);
                }
                
                // Kiểm tra xem nhân viên đã được chỉ định cho ca làm việc trong cùng một ngày hay chưa
                $existingWorkSchedule = WorkSchedule::where('date', $workSchedule['date'])
                                                      ->where('work_shift_id', $workSchedule['work_shift_id'])
                                                      ->where('employee_id', $workSchedule['employee_id'])
                                                      ->first();
    
                if ($existingWorkSchedule) {
                    $existingWorkSchedule->delete();
                }
    
                // Tạo một bản ghi mới
                $data = [
                    'date' => $workSchedule['date'],
                    'work_shift_id' => $workSchedule['work_shift_id'],
                    'employee_id' => $workSchedule['employee_id'],
                    'store_id' => $storeId,
                    'status'=> true,
                ];
    
                WorkSchedule::create($data);
            }
    
            return response()->json([
                'message' => 'Create successful work schedules',
                'data' => $request->all(),
            ], 201);
        } else {
            return response()->json([
                'message' => 'Error',
            ], 404);
        }
    }
    
    
    

    /**
     * Display the specified resource.
     */
    public function show(Request $request, String $date)
    {
        // $workShiftId = intval($workShiftId);
        // $employeeId = intval($employeeId);
        $token = $request->bearerToken();
        $managerId = JWTAuth::getPayload($token)->get('sub');
    
        $store = Store::where('manager_id', $managerId)->first();
    
        if ($store) {
            $storeId = $store->id;
    
            $workSchedules = WorkSchedule::with('employee.part','employee.information', 'workShift')
                ->where('store_id', $storeId)
                ->where('date', $date)
                // ->where('work_shift_id', $workShiftId)
                ->get();
    
            return response()->json($workSchedules, 200);
        } else {
            return response()->json(['message' => 'Không tìm thấy cửa hàng'], 404);
        }
    }

    public function showWorkSchedule(Request $request, String $date)
    {

        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');
    
        $employee = Employee::with('store')->where('user_id', $userId)->first();

        if ($employee) {
            $storeId = $employee->store->id;
    
            $workSchedules = WorkSchedule::with('employee.part','employee.information', 'workShift')
                ->where('store_id', $storeId)
                ->where('date', $date)
                // ->where('work_shift_id', $workShiftId)
                ->get();
    
            return response()->json($workSchedules, 200);
        } else {
            return response()->json(['message' => 'Không tìm thấy cửa hàng'], 404);
        }
    }

    public function personalWorkSchedule(Request $request, String $date)
    {

        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');
    
        $employee = Employee::where('user_id', $userId)->first();


        if ($employee && $date) {
            $employeeId = $employee->id;
        
            // Chuyển đổi $date thành đối tượng Carbon
            $startDate = Carbon::parse($date);
        
            // Tính toán ngày kết thúc bằng cách thêm 7 ngày vào ngày bắt đầu
            $endDate = $startDate->copy()->addDays(6); // Thêm 6 ngày để có tổng cộng 7 ngày
        
            $workSchedules = WorkSchedule::with('employee.part', 'workShift')
                ->where('employee_id', $employeeId)
                ->where('status',1)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();
        
            return response()->json($workSchedules, 200);
        }
        else {
            return response()->json(['message' => 'Không tìm thấy cửa hàng'], 404);
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
