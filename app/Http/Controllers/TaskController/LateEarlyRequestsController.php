<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;
use App\Models\LateEarlyRequests;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Store;
use App\Models\Employee;
use App\Models\WorkSchedule;
use App\Models\Noitification;
use App\Models\Attendance;
use App\Models\WorkShift;
use App\Http\Controllers\Notification\NotificationController;

class LateEarlyRequestsController extends Controller
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
            $lateEarly = LateEarlyRequests::with('workSchedule.employee.information', 'workSchedule.workShift')
            ->where('store_id', $storeId)
            ->where('status', 'pending')
            ->get();

            return response()->json($lateEarly->all(), 200);
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
        
    }

    public function late(Request $request)
    {
        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');

        $employee = Employee::where('user_id', $userId)->firstOrFail();
        $storeId = $employee->store_id;
        $store = Store::findOrFail($storeId);
        $manageId = $store->manager_id;

        $manager = User::findOrFail($manageId);
        $device_key = $manager->token_device;

        $existLeave = LeaveRequest::where('work_schedule_id', $request->work_schedule_id)
        ->exists();
        $existLate = LateEarlyRequests::where('work_schedule_id', $request->work_schedule_id)
        ->where('status', 'pending')
        ->where('type', 'late')
        ->exists();
        if ($existLeave) {
            return response()->json([
                'message' => 'Bạn đã xin nghỉ ca làm này rồi.',
                'data' => $request->all(),
            ], 400);
        }
        if ($existLate) {
            return response()->json([
                'message' => 'Bạn đã xin đi muộn ca làm này rồi.',
                'data' => $request->all(),
            ], 400);
        }

        $data = [
            'work_schedule_id' => $request->work_schedule_id,
            'reason' => $request->reason,
            'type' => 'late',
            'time' => $request->time,
            'store_id' =>$storeId,
        ];

        $result = LateEarlyRequests::create($data);

        if ($result) {
            $workSchedule = WorkSchedule::with('employee.part', 'employee.information', 'workShift')
                ->findOrFail($request->work_schedule_id);
            $employeeName = $workSchedule->employee->information->ho_ten;
            $date = $workSchedule->date;
            $workShift = $workSchedule->workShift->name;

            $title = "Xin đi muộn";
            $body = "Nhân viên $employeeName xin đi muộn $workShift ngày $date";
            $model = $result->id;
            NotificationController::notify($title, $body, $device_key, $model);
            NotificationController::addNotify($title, $body, 'late_early_requests', $manageId, $result->id);

            return response()->json([
                'message' => 'Xin đi muộn thành công',
                'data' => $result,
            ], 201);
        } else {
            return response()->json([
                'message' => 'Error',
            ], 400);
        }

    }

    public function early(Request $request)
    {
        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');

        $employee = Employee::where('user_id', $userId)->firstOrFail();
        $storeId = $employee->store_id;

        $store = Store::findOrFail($storeId);
        $manageId = $store->manager_id;

        $manager = User::findOrFail($manageId);
        $device_key = $manager->token_device;

        $existLeave = LeaveRequest::where('work_schedule_id', $request->work_schedule_id)
        ->exists();
        $existLate = LateEarlyRequests::where('work_schedule_id', $request->work_schedule_id)
        ->where('status', 'pending')
        ->where('type', 'early')
        ->exists();
        if ($existLeave) {
            return response()->json([
                'message' => 'Bạn đã xin nghỉ ca làm này rồi.',
                'data' => $request->all(),
            ], 400);
        }
        if ($existLate) {
            return response()->json([
                'message' => 'Bạn đã xin về sớm ca làm này rồi.',
                'data' => $request->all(),
            ], 400);
        }

        $data = [
            'work_schedule_id' => $request->work_schedule_id,
            'reason' => $request->reason,
            'type' => 'early',
            'time' => $request->time,
            'store_id' =>$storeId,
        ];

        $result = LateEarlyRequests::create($data);

        if ($result) {
            $workSchedule = WorkSchedule::with('employee.part', 'employee.information', 'workShift')
                ->findOrFail($request->work_schedule_id);
            $employeeName = $workSchedule->employee->information->ho_ten;
            $date = $workSchedule->date;
            $workShift = $workSchedule->workShift->name;

            $title = "Xin về sớm";
            $body = "Nhân viên $employeeName xin về sớm $workShift ngày $date";
            $model = $result->id;
            NotificationController::notify($title, $body, $device_key, $model);
            NotificationController::addNotify($title, $body, 'late_early_requests', $manageId, $result->id);

            return response()->json([
                'message' => 'Xin sớm thành công',
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
    public function update(Request $request)
     {
            $token = $request->bearerToken();
            $manage_id = JWTAuth::getPayload($token)->get('sub');

            $store = Store::where('manager_id', $manage_id)->first();

            if ($store) {
                $storeId = $store->id;
                $lateEarly = LateEarlyRequests::find($request->id);
                if ($lateEarly) {
                    $workSchedule = WorkSchedule::where('id', $lateEarly->work_schedule_id)->first();
                    $workShift = WorkShift::where('id',$workSchedule->work_shift_id)->first();
                    $employeeId = $workSchedule->employee_id;
                    $employee = Employee::where('id', $employeeId)->first();
                    $userId = $employee->user_id;
                    $lateEarly->update(['status' => $request->status]);
                    $title = "Xin đi muộn/về sớm";
                    $body = "Quản lý đã duyệt yêu cầu xin đi muộn/về sớm $workShift->name ngày $workSchedule->date";
                    NotificationController::addNotify($title, $body, 'leave_requests', $userId, $request->id);
                    if($lateEarly->type == 'early'){
                        if($request->status == 'approved'){
                            $attendances = Attendance::where('work_schedule', $lateEarly->work_schedule_id)->first();
                        if($attendances){
                            $attendances->update(['check_out'=> $lateEarly->time ]);
                        }
                        }
                    }
                    return response()->json([
                        'message' => 'Update successful lateEarly',
                        'data' => $lateEarly
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'LateEarly not found',
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
            $lateEarlyRequest = LateEarlyRequests::findOrFail($id);
            $noti = Noitification::where('type', 'exit_requests')
            ->where('reference_id', $lateEarlyRequest->id);
            $lateEarlyRequest->delete();
            $noti->delete();
            return response()->json(['message' => 'Hủy thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hủy không thành công'], 500);
        }
    }

    public function showLate(string $workScheduleId)
    {
        $lateRequest = LateEarlyRequests::where('work_schedule_id', $workScheduleId)
        ->with('workSchedule.workShift')
        ->where('type', 'late')
        ->first();

        if($lateRequest){
        return response()->json($lateRequest, 200);
        }else{
            return response()->json($lateRequest, 400);
        }
    }

    public function showEarly(string $workScheduleId)
    {
        $earlyRequest = LateEarlyRequests::where('work_schedule_id', $workScheduleId)
        ->with('workSchedule.workShift')
        ->where('type', 'early')
        ->first();

        if($earlyRequest){
        return response()->json($earlyRequest, 200);
        }else{
            return response()->json($earlyRequest, 400);
        }
    }
}
