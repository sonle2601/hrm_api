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
use App\Models\Noitification;
use App\Models\WorkShift;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Http\Controllers\Notification\NotificationController;




class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($employeeId, $date)
    {
        // Kiểm tra xem employeeId đã được truyền vào không
            if ($employeeId) {
            $workSchedules = WorkSchedule::where('employee_id', $employeeId)->get();
            $attendances = [];

            // $today = Carbon::now();
            $startDate = Carbon::createFromDate($date)->startOfMonth();
            $endDate = Carbon::createFromDate($date)->endOfMonth();

            
            foreach ($workSchedules as $workSchedule) {
                // Lấy thông tin chấm công dựa trên work_schedule_id
                $attendance = Attendance::where('work_schedule', $workSchedule->id)
                    ->where('status', 'approved')
                    ->whereBetween('date', [$startDate, $endDate])
                    ->with('workSchedule.workShift')
                    ->get();

                if ($attendance->isNotEmpty()) {
                    $attendances = array_merge($attendances, $attendance->toArray());
                }
            }

            if (!empty($attendances)) {
                return response()->json($attendances, 200);
                } else{
                return response()->json($attendances, 200);

                }
            } 

    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function showAttendanceRequest(Request $request) 
    {
        $token = $request->bearerToken();
        $manage_id = JWTAuth::getPayload($token)->get('sub');
    
        $store = Store::where('manager_id', $manage_id)->first();

        $workSchedules = WorkSchedule::where('store_id', $store->id)->get();
        $attendances = [];

        foreach ($workSchedules as $workSchedule) {
            $attendance = Attendance::where('work_schedule', $workSchedule->id)
                ->where('status', 'pending')
                ->with('workSchedule.employee.information', 'workSchedule.workShift')
                ->get();

            if ($attendance->isNotEmpty()) {
                $attendances = array_merge($attendances, $attendance->toArray());
            }
        }

        if (!empty($attendances)) {
            return response()->json($attendances, 200);
        } else {
            return response()->json($attendances, 200);

                }
        
    }

    public function destroy(string $id)
    {
        try {
            $attendanceRequest = Attendance::findOrFail($id);
            $noti = Noitification::where('type', 'attendance_requests')
            ->where('reference_id', $attendanceRequest->id);
            $attendanceRequest->delete();
            $noti->delete();
            return response()->json(['message' => 'Hủy chấm công bổ sung thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hủy chấm công bổ sung không thành công'], 500);
        }
    }

    public function getAttendanceRequest(string $workScheduleId)
    {
        $attendanceRequest = Attendance::where('work_schedule', $workScheduleId)
        ->with('workSchedule.workShift')
        ->where('status', 'pending')
        ->first();

        if($attendanceRequest){
        return response()->json($attendanceRequest, 200);
        }else{
            return response()->json($attendanceRequest, 400);
        }
    }
     
    public function attendanceRequest(Request $request) 
    {
        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');
        $today = Carbon::now()->toDateString();
        $employee = Employee::where('user_id', $userId)->firstOrFail();
        $storeId = $employee->store_id;

        $store = Store::findOrFail($storeId);
        $manageId = $store->manager_id;

        $manager = User::findOrFail($manageId);
        $device_key = $manager->token_device;

        $workScheduleId = $request->work_schedule_id;

        $data =  [
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'minutes_out' => $request->minutes_out,
            'work_schedule' => $workScheduleId,
            'date' => $today,
            'status' => 'pending',
        ];

        $existsLeave = LeaveRequest::where('work_schedule_id', $request->work_schedule_id)
        ->where('status', 'accept')
        ->exists();
        if ($existsLeave) {
            return response()->json([
                'message' => 'Bạn đã xin nghỉ ca làm việc này trước đó.',
                'data' => $request->all(),
            ], 400);
        }
        $existingAttendance = Attendance::where('work_schedule', $workScheduleId)
                            ->exists();
        if ($existingAttendance) {
            return response()->json([
            'message' => 'Bạn đã chấm công cho ca làm việc này rồi!',
            ], 400); 
            }                    
        
        $attendance = Attendance::create($data);
        if ($attendance) {
            $workSchedule = WorkSchedule::with('employee.part', 'employee.information', 'workShift')
            ->findOrFail($request->work_schedule_id);
            $employeeName = $workSchedule->employee->information->ho_ten;
            $date = $workSchedule->date;
            $workShift = $workSchedule->workShift->name;

            $title = "Chấm công bổ sung";
            $body = "Nhân viên $employeeName xin chấm công bổ sung $workShift ngày $date";
            $model = $attendance->id;
            NotificationController::notify($title, $body, $device_key, $model);
            NotificationController::addNotify($title, $body, 'attendance_requests', $manageId,$attendance->id);

            return response()->json([
                'message' => 'Chấm công thành công!',
                'attendance' => $attendance,
            ], 201); // Created
        } else {
            return response()->json([
                'message' => 'Chấm công thất bại!',
            ], 500); // Internal Server Error
        }                
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');
        $today = Carbon::now()->toDateString();
        $macAddress = $request->mac_address;
        $nameDevice = $request->name_device;
        
        $employee = Employee::where('user_id', $userId)->first();
        $timeClockDevice = TimeClockDevice::where('store_id', $employee->store_id)->first();

        $workSchedules = WorkSchedule::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->where('status', '1')
            ->get();
        
        
            if (!is_null($timeClockDevice)) {
                if($nameDevice == $timeClockDevice->device_name && $macAddress == $timeClockDevice->mac_address){
                    if ($workSchedules->isEmpty()) { 
                        return response()->json([
                            'message' => 'Hôm nay bạn không có lịch!',
                        ], 404);
                    } else {
                    foreach ($workSchedules as $workSchedule) {
                        $workShift = WorkShift::find($workSchedule->work_shift_id);
                        $startTime = Carbon::parse($workShift->start_time);
                        $endTime = Carbon::parse($workShift->end_time);

                    if (Carbon::now()->between($startTime, $endTime)) {
                        $existingAttendance = Attendance::where('work_schedule', $workSchedule->id)
                            ->whereBetween('check_in', [$startTime, $endTime])
                            ->exists();

                        if ($existingAttendance) {
                            return response()->json([
                                'message' => 'Bạn đã chấm công cho ca làm việc này rồi!',
                            ], 400); 
                        }

                        $currentTime = Carbon::now();

                        $data =  [
                            'check_in' => $currentTime,
                            'check_out' => $endTime,
                            'minutes_out' => 0,
                            'work_schedule' => $workSchedule->id,
                            'date' => $today,
                            'status' => 'approved',
                        ];
                        $attendance = Attendance::create($data);
                        if ($attendance) {
                            return response()->json([
                                'message' => 'Chấm công thành công!',
                                'attendance' => $attendance,
                            ], 201); // Created
                        } else {
                            return response()->json([
                                'message' => 'Chấm công thất bại!',
                            ], 500);
                        }
                    }
                }

                $nextWorkShift = $workSchedules->first(function ($workSchedule) {
                    $startTime = Carbon::parse($workSchedule->workShift->start_time);
                    return Carbon::now()->lt($startTime);
                });

                if ($nextWorkShift) {
                    return response()->json([
                        'message' => 'Chưa đến thời gian chấm công cho ca tiếp theo!',
                    ], 404);
                } else {
                    return response()->json([
                        'message' => 'Chưa đến thời gian chấm công!',
                    ], 404);
                }

                        
                    }
                }else{
                    return response()->json([
                        'message' => 'Vui lòng kết nối đúng wifi được chỉ định để chấm công!',
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'Chưa có thiết bị chấm công!',
                ], 404);
            }
            
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $date)
        {
            $token = $request->bearerToken();
            $userId = JWTAuth::getPayload($token)->get('sub');
            $employee = Employee::where('user_id', $userId)->firstOrFail();
            $employeeId = $employee->id;
                if ($employeeId) {
                $workSchedules = WorkSchedule::where('employee_id', $employeeId)->get();
                $attendances = [];
    
                // $today = Carbon::now();
                $startDate = Carbon::createFromDate($date)->startOfMonth();
                $endDate = Carbon::createFromDate($date)->endOfMonth();
    
                
                foreach ($workSchedules as $workSchedule) {
                    // Lấy thông tin chấm công dựa trên work_schedule_id
                    $attendance = Attendance::where('work_schedule', $workSchedule->id)
                        ->where('status', 'approved')
                        ->whereBetween('date', [$startDate, $endDate])
                        ->with('workSchedule.workShift')
                        ->get();
    
                    if ($attendance->isNotEmpty()) {
                        $attendances = array_merge($attendances, $attendance->toArray());
                    }
                }
    
                if (!empty($attendances)) {
                    return response()->json($attendances, 200);
                } else {
                    return response()->json($attendances, 200);
                }
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
                $attendance = Attendance::find($request->id);
                if ($attendance) {
                    $workSchedule = WorkSchedule::where('id', $attendance->work_schedule)->first();
                    $workShift = WorkShift::where('id',$workSchedule->work_shift_id)->first();
                    $employeeId = $workSchedule->employee_id;
                    $employee = Employee::where('id', $employeeId)->first();
                    $attendance->update(['status' => $request->status]);
                    $userId = $employee->user_id;
                    $title = "Chấm công bổ sung";
                    $body = "Quản lý đã duyệt chấm công bổ sung $workShift->name ngày $workSchedule->date";
                    NotificationController::addNotify($title, $body, 'leave_requests', $userId, $request->id);
                    return response()->json([
                        'message' => 'Update successful attendance',
                        'data' => $attendance
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'attendance not found',
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'Error: Store not found',
                ], 404);
            }
    }

   
}
