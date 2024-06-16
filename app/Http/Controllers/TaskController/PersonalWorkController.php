<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Employee;
use App\Models\Store;
use App\Models\WorkSchedule;
use App\Models\TimeClockDevice;
use App\Models\Information;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\WorkShift;
use App\Models\Attendance;

class PersonalWorkController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function getAvailableEmployees(Request $request, $work_shift_id, $date)
     {
        $token = $request->bearerToken();
        $managerId = JWTAuth::getPayload($token)->get('sub');
        
        $store = Store::where('manager_id', $managerId)->firstOrFail();
        $storeId = $store->id;
        
        // Lấy ra thông tin lịch làm việc nếu có
        $workSchedules = WorkSchedule::where('date', $date)
            ->where('work_shift_id', $work_shift_id)
            ->get();

            // $workSchedule = WorkSchedule::where('date', $date)
            // ->where('work_shift_id', $work_shift_id)
            // ->first();
        
            // $availableEmployeesQuery = Employee::where('store_id', $storeId);

            // // Nếu có lịch làm việc, loại bỏ nhân viên đã được phân công
            // if ($workSchedule) {
            //     $availableEmployeesQuery->whereNotIn('id', [$workSchedule->employee_id]);
            // }
        
            // // Lấy danh sách nhân viên có sẵn
            // $availableEmployees = $availableEmployeesQuery->with('user.information')->get();
        
            // // Trả về danh sách nhân viên có sẵn kèm theo thông tin cá nhân
            // return response()->json($availableEmployees);
        
        $availableEmployeesQuery = Employee::where('store_id', $storeId)
        ->where('employee_status', 1)
        ;
        
        foreach ($workSchedules as $workSchedule) {
            if ($workSchedule) {
                $employeeId = $workSchedule->employee_id;
                $availableEmployeesQuery->where('id', '!=', $employeeId);
            }
        }
       
        $availableEmployees = $availableEmployeesQuery->get();
        
        // Lặp qua mỗi nhân viên để lấy thông tin tương ứng từ bảng Information
        foreach ($availableEmployees as $employee) {
            $userId = $employee->user_id;
            
            $information = Information::where('user_id', $userId)->first();
            $employee->information = $information;
        }
        
        // Trả về JSON
        return response()->json($availableEmployees);
        

     }
     
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
        //
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


    public function checkWorkSchedule(string $id) {
        // Lấy thời gian hiện tại của hệ thống
        $currentTime = Carbon::now();
    
        $workSchedule = WorkSchedule::where('id', $id)
                                    ->with('workShift')
                                    ->first();
    
        // Kiểm tra nếu workSchedule tồn tại và có workShift
        if ($workSchedule && $workSchedule->workShift) {
            $workScheduleDate = $workSchedule->date;
            $startTime = $workSchedule->workShift->start_time;
            $endTime = $workSchedule->workShift->end_time;
            $startTimeWork = Carbon::parse($workScheduleDate . ' ' . $startTime);
            $endTimeWork = Carbon::parse($workScheduleDate . ' ' . $endTime);
    
            if ($currentTime->lt($startTimeWork)) {
                return response()->json(['status' => 'before']);
            } elseif ($currentTime->between($startTimeWork, $endTimeWork)) {
                // Thời gian hiện tại trong khoảng thời gian workShift
                return response()->json(['status' => 'during']);
            } else {
                // Thời gian hiện tại sau thời gian workShift kết thúc
                return response()->json(['status' => 'after']);
            }
        } else {
            // Xử lý trường hợp workSchedule không tồn tại hoặc không có workShift
            return response()->json(['status' => 'error', 'message' => 'WorkSchedule hoặc WorkShift không tồn tại.'], 404);
        }
    }
}
