<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Store;
use App\Models\Employee;
use App\Models\WorkSchedule;
use App\Models\Attendance;
use App\Models\Noitification;
use App\Http\Controllers\Notification\NotificationController;
use App\Models\WorkShift;
use App\Models\LateEarlyRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;



class LeaveRequestController extends Controller
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
            $leave = LeaveRequest::with('workSchedule.employee.information', 'workSchedule.workShift')
            ->where('store_id', $storeId)
            ->where('status', 'waiting')
            ->get();

            return response()->json($leave->all(), 200);
        } else {
                    return response()->json([
                        'message' => 'Error: Store not found',
                    ], 404);
                }   

    }


    public function dailyReport(Request $request, string $date)
    {
        // Lấy token từ request
        $token = $request->bearerToken();
        
        // Lấy ID quản lý từ token
        $managerId = JWTAuth::getPayload($token)->get('sub');
        
        // Lấy cửa hàng theo manager_id
        $store = Store::where('manager_id', $managerId)->first();
        
        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }
        
        $storeId = $store->id;
        
        // Lấy danh sách work_schedules theo date và store_id
        $workSchedules = WorkSchedule::where('date', $date)
                                    ->with('workShift', 'employee.information')
                                     ->where('store_id', $storeId)
                                     ->where('status', 1) // Lọc những work_schedules với status là 1 (đi làm)
                                     ->get();
        if(!$workSchedules){
            return response()->json($workSchedules, 200);
        }                            
        $approvedLeaveEmployees  = WorkSchedule::where('date', $date)
        ->with('workShift', 'employee.information')
         ->where('store_id', $storeId)
         ->where('status', 0) // Lọc những work_schedules với status là 1 (đi làm)
         ->get();
        
        $approvedEarlyEmployees =[];

        $approvedLateEmployees = [];
        
        
        // Danh sách nhân viên nghỉ không phép
        $unapprovedLeaveEmployees = [];
        
        // Danh sách nhân viên đi làm trễ
        $unapprovedLateEmployees = [];
        
        // Lấy thời gian hiện tại
        $currentTime = Carbon::now();
        
        // Kiểm tra từng work_schedule
        foreach ($workSchedules as $workSchedule) {
            // Lấy ca làm việc
            $workShift = WorkShift::find($workSchedule->work_shift_id);

            $late = LateEarlyRequests::where('work_schedule_id', $workSchedule->id)
                ->where('type', 'late')
                ->first();
            $early = LateEarlyRequests::where('work_schedule_id', $workSchedule->id)
                ->where('type', 'early')
                ->first();  
      
                if ($late) {
                    $approvedLateEmployees[] = $workSchedule;
                }
                
                if ($early) {
                    $approvedEarlyEmployees[] = $workSchedule;
                }  
            
            if (!$workShift) {
                continue;
            }
            
            // Lấy thời gian bắt đầu và kết thúc ca làm việc
            $startTime = Carbon::parse($workShift->start_time);
            $endTime = Carbon::parse($workShift->end_time);
            
            // Kiểm tra trạng thái đi làm
            if ($currentTime->greaterThanOrEqualTo($startTime) && !$currentTime->greaterThanOrEqualTo($endTime)) {
                // Kiểm tra chấm công
                $attendance = Attendance::where('work_schedule', $workSchedule->id)->first();
                
                // Kiểm tra đi làm trễ
                $late = LateEarlyRequests::where('work_schedule_id', $workSchedule->id)
                ->where('type', 'late')
                ->first();
                
                // Kiểm tra nếu nhân viên không chấm công và không đi làm trễ
                if (!$attendance && !$late) {
                    // Lấy thông tin nhân viên từ work_schedule
                    // $employee = $workSchedule->employee; // Giả sử có mối quan hệ giữa work_schedule và employee
                    
                        $unapprovedLateEmployees[] = $workSchedule;
                }
            }
            
            // Kiểm tra nếu thời gian hiện tại đã qua thời gian kết thúc ca làm việc
            if ($currentTime->greaterThan($endTime)) {
                // Kiểm tra chấm công
                $attendance = Attendance::where('work_schedule', $workSchedule->id)->first();
                
                // Nếu không có dữ liệu chấm công, tức là nghỉ không phép
                if (!$attendance) {
                    // Lấy thông tin nhân viên từ work_schedule
                    // $employee = $workSchedule->employee;
                    
                        $unapprovedLeaveEmployees[] = $workSchedule;
                }
            }

            
            
        }

        $countUnapprovedLeaveEmployees = count($unapprovedLeaveEmployees);
        $countUnapprovedLateEmployees = count($unapprovedLateEmployees);
        $countApprovedLeaveEmployees = count($approvedLeaveEmployees);
        $countApprovedLateEmployees = count($approvedLateEmployees);
        $countApprovedEarlyEmployees = count($approvedEarlyEmployees);

        // Trả về danh sách các nhân viên nghỉ không phép và đi làm trễ
        return response()->json([
            'date' => $date,
            'store_id' => $storeId,
            'unapproved_leave_employees' => $unapprovedLeaveEmployees,
            'unapproved_late_employees' => $unapprovedLateEmployees,
            'approved_leave_employees' => $approvedLeaveEmployees,
            'approved_late_employees' => $approvedLateEmployees,
            'approved_early_employees' => $approvedEarlyEmployees,
            'counts' => [
                'unapproved_leave_count' => $countUnapprovedLeaveEmployees,
                'unapproved_late_count' => $countUnapprovedLateEmployees,
                'approved_leave_count' => $countApprovedLeaveEmployees,
                'approved_late_count' => $countApprovedLateEmployees,
                'approved_early_count' => $countApprovedEarlyEmployees,
            ],
        ]);
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
        $userId = JWTAuth::getPayload($token)->get('sub');

        $employee = Employee::where('user_id', $userId)->firstOrFail();
        $storeId = $employee->store_id;
        $store = Store::findOrFail($storeId);
        $manageId = $store->manager_id;

        $manager = User::findOrFail($manageId);
        $device_key = $manager->token_device;

        $exists = LeaveRequest::where('work_schedule_id', $request->work_schedule_id)->exists();
        if ($exists) {
            return response()->json([
                'message' => 'Bạn đã xin nghỉ ca làm việc này trước đó.',
                'data' => $request->all(),
            ], 400);
        }


        $data = [
            'work_schedule_id' => $request->work_schedule_id,
            'reason' => $request->reason,
            'status' => 'waiting',
            'store_id' =>$storeId,
        ];

        $result = LeaveRequest::create($data);
        // dd($request->work_schedule_id);


        if ($result) {
            $workSchedule = WorkSchedule::with('employee.part', 'employee.information', 'workShift')
                ->findOrFail($request->work_schedule_id);
            $employeeName = $workSchedule->employee->information->ho_ten;
            $date = $workSchedule->date;
            $workShift = $workSchedule->workShift->name;

            $title = "Xin nghỉ ca";
            $body = "Nhân viên $employeeName xin nghỉ $workShift ngày $date";
            $model = $result->id;
            NotificationController::notify($title, $body, $device_key, $model);
            NotificationController::addNotify($title, $body, 'leave_requests', $manageId, $result->id);

            return response()->json([
                'message' => 'Create a successful parts',
                'data' => $result,
            ], 201);
        } else {
            return response()->json([
                'message' => 'Error',
            ], 400);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $workScheduleId)
    {
        $leaveRequest = LeaveRequest::where('work_schedule_id', $workScheduleId)
        ->with('workSchedule.workShift')
        ->first();

        if($leaveRequest){
        return response()->json($leaveRequest, 200);
        }else{
            return response()->json($leaveRequest, 400);
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
                $leave = LeaveRequest::find($request->id);
                
                if ($leave) {
                $workSchedule = WorkSchedule::where('id', $leave->work_schedule_id)->first();
                $workShift = WorkShift::where('id',$workSchedule->work_shift_id)->first();
                $employeeId = $workSchedule->employee_id;
                $employee = Employee::where('id', $employeeId)->first();
                $userId = $employee->user_id;
                    $leave->update(['status' => $request->status]);
                    $title = "Xin nghỉ ca";
                    $body = "Quản lý đã duyệt xin nghỉ $workShift->name ngày $workSchedule->date";
                    NotificationController::addNotify($title, $body, 'leave_requests', $userId, $request->id);
                    if($request->status = 'accept'){
                        $workSchedule = WorkSchedule::where('id', $leave->work_schedule_id)->first();
                        $workSchedule->update(['status' => 0]);

                       if($request->employee_id != null){
                        $data = [
                            'date' => $request->date,
                            'work_shift_id' => $request->work_shift_id,
                            'employee_id' => $request->employee_id,
                            'store_id' => $storeId,
                            'status'=> true,
                        ];
                        WorkSchedule::create($data);
                       }
                    }

                    return response()->json([
                        'message' => 'Update successful leave',
                        'data' => $leave
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Leave not found',
                    ], 404);
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
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);
            $noti = Noitification::where('type', 'leave_requests')
            ->where('reference_id', $leaveRequest->id);
            $leaveRequest->delete();
            $noti->delete();
            return response()->json(['message' => 'Hủy đăng kí nghỉ thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hủy đăng kí nghỉ không thành công'], 500);
        }
    }
}
