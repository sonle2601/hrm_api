<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;
use App\Models\ExitRequest;
use App\Models\User;
use App\Models\Store;
use App\Models\Employee;
use App\Models\WorkSchedule;
use App\Models\LeaveRequest;
use App\Models\Noitification;
use App\Models\Attendance;
use App\Http\Controllers\Notification\NotificationController;
use App\Models\WorkShift;


class ExitRequestsController extends Controller
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
            $exit = ExitRequest::with('workSchedule.employee.information', 'workSchedule.workShift')
            ->where('store_id', $storeId)
            ->where('status', 'pending')
            ->get();

            return response()->json($exit->all(), 200);
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
        $userId = JWTAuth::getPayload($token)->get('sub');

        $employee = Employee::where('user_id', $userId)->firstOrFail();
        $storeId = $employee->store_id;
        $store = Store::findOrFail($storeId);
        $manageId = $store->manager_id;

        $manager = User::findOrFail($manageId);
        $device_key = $manager->token_device;

        $existsLeave = LeaveRequest::where('work_schedule_id', $request->work_schedule_id)->exists();
        if ($existsLeave) {
            return response()->json([
                'message' => 'Bạn đã xin nghỉ ca làm việc này trước đó.',
                'data' => $request->all(),
            ], 400);
        }

        $existsExit = ExitRequest::where('work_schedule_id', $request->work_schedule_id)->exists();
        if ($existsExit) {
            return response()->json([
                'message' => 'Bạn đã xin ra ngoài ca làm việc này trước đó.',
                'data' => $request->all(),
            ], 400);
        }

        $data = [
            'work_schedule_id' => $request->work_schedule_id,
            'reason' => $request->reason,
            'status' => 'pending',
            'store_id' =>$storeId,
            'minutes_out'=>$request->minutes_out,
        ];

        $result = ExitRequest::create($data);

        if ($result) {
            $workSchedule = WorkSchedule::with('employee.part', 'employee.information', 'workShift')
                ->findOrFail($request->work_schedule_id);
            $employeeName = $workSchedule->employee->information->ho_ten;
            $date = $workSchedule->date;
            $workShift = $workSchedule->workShift->name;

            $title = "Xin nghỉ ca";
            $body = "Nhân viên $employeeName xin ra ngoài $workShift ngày $date";
            $model = $result->id;
            NotificationController::notify($title, $body, $device_key, $model);
            NotificationController::addNotify($title, $body, 'exit_requests', $manageId, $result->id);

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
        $exitRequest = ExitRequest::where('work_schedule_id', $workScheduleId)
        ->with('workSchedule.workShift')
        ->first();

        if($exitRequest){
        return response()->json($exitRequest, 200);
        }else{
            return response()->json($exitRequest, 400);
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
            $exit = ExitRequest::find($request->id);
            if ($exit) {
                $workSchedule = WorkSchedule::where('id', $exit->work_schedule_id)->first();
                $workShift = WorkShift::where('id',$workSchedule->work_shift_id)->first();
                $employeeId = $workSchedule->employee_id;
                $employee = Employee::where('id', $employeeId)->first();
                $userId = $employee->user_id;
                $exit->update(['status' => $request->status]);
                $title = "Xin ra ngoài";
                $body = "Quản lý đã xin ra ngoài $workShift->name ngày $workSchedule->date";
                NotificationController::addNotify($title, $body, 'leave_requests', $userId, $request->id);
                if($request->status == 'approved'){
                    $attendances = Attendance::where('work_schedule', $exit->work_schedule_id)->first();
                    if($attendances){
                        $attendances->update(['minutes_out'=> $exit->minutes_out ]);
                    }
                }

                return response()->json([
                    'message' => 'Update successful exit',
                    'data' => $exit
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
            $exitRequest = ExitRequest::findOrFail($id);
            $noti = Noitification::where('type', 'exit_requests')
            ->where('reference_id', $exitRequest->id);
            $exitRequest->delete();
            $noti->delete();
            return response()->json(['message' => 'Hủy đăng kí ra ngoài thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hủy đăng kí ra ngoài không thành công'], 500);
        }
    }
}
