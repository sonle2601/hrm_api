<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Store;
use App\Models\WorkSchedule;
use App\Models\SalaryBonus;
use App\Models\SalaryPenalty;
use App\Models\Information;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\WorkShift;
use App\Models\Noitification;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\LeaveRequest;
use App\Models\ExitRequest;
use App\Models\LateEarlyRequests;
use App\Models\Attendance;




class NotificationController extends Controller
{
    static public function notify($title, $body, $device_key, $model){
        // dd($device_key);

        $url = 'https://fcm.googleapis.com/fcm/send';
        $serverKey = 'AAAAYT7Bsyo:APA91bGuhLONo0pkfhHyL9l_AM6xIFNsDcWFFAJs5tr8hIvK5MZsg2gwyKjpdJ7UhAfJ_AYbb90vKYX2x4Q_QwPZo9rdlN9v31JqL4v8OuLoaZBim6F4kCtNHdqdU8Og9LENTZQEiY0K';

        // $dataArr = [
        //     'click_action' =>'FLUTTER_NOTIFICATION_CLICK',
        //     'status' => 'daone',
        // ];

        $data = [
            "to" => $device_key,
            'notification' => [
                "title"=>$title,
                "body" =>$body,
                "sound" =>"default"
            ],
            "data" => [
                "model_id" => $model, // Gán giá trị của $model cho một khóa cụ thể trong phần "data"
            ],
            "priority" => "high"
        ];




        $encodedData = json_encode($data);


        $headers = array(
            "Authorization:key=" . $serverKey,
            "Content-Type: application/json",
        );


        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

        $result = curl_exec($ch);

        if ($result === FALSE) {
            return [
                'message' =>'failed',
                'r' => $result,
                'success' =>false,
            ];
        } 

        curl_close($ch);

        return [
            'message' =>'success',
            'r' => $result,
            'success' =>true,
        ];
    }

    public function index(Request $request) {
        $token = $request->bearerToken();
        $manage_id = JWTAuth::getPayload($token)->get('sub');
    
        $notifications = Noitification::where('user_id', $manage_id)
            ->with('leaveRequest.workSchedule.employee.information',
            'leaveRequest.workSchedule.workShift', 
            'exitRequest.workSchedule.employee.information',
            'exitRequest.workSchedule.workShift',
             'lateEarlyRequests.workSchedule.employee.information',
            'lateEarlyRequests.workSchedule.workShift',
              'attendanceRequest.workSchedule.employee.information',
            'attendanceRequest.workSchedule.workShift',
              )
              ->orderBy('created_at', 'desc')
            ->get();
    
        return response()->json($notifications, 200);
    }

    static public function addNotify($title, $content, $type, $user_id, $reference_id){
        $data = [
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'user_id' => $user_id,
            'reference_id' => $reference_id,
        ];

        Noitification::create($data);
    }

    public function countApprove(Request $request)
    {
        // Lấy token từ request
        $token = $request->bearerToken();
        // Lấy ID quản lý từ token
        $managerId = JWTAuth::getPayload($token)->get('sub');
        // Lấy cửa hàng theo manager_id
        $store = Store::where('manager_id', $managerId)->first();

        $workScheduleIds  = WorkSchedule::where('store_id', $store->id)->pluck('id');;
        // Nếu không tìm thấy cửa hàng, trả về thông báo lỗi
        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }
    
        // Tính số lượng yêu cầu chờ phê duyệt
        $countLeaveRequest = LeaveRequest::where('store_id', $store->id)
                                ->where('status', 'waiting')
                                ->count();
    
        $countExitRequest = ExitRequest::where('store_id', $store->id)
                             ->where('status', 'pending')
                             ->count();
    
        $countLateEarlyRequest = LateEarlyRequests::where('store_id', $store->id)
                                ->where('status', 'pending')
                                ->count();
    
        $countAttendanceRequest = Attendance::whereIn('work_schedule', $workScheduleIds)
                                    ->where('status', 'pending')
                                    ->count();
    
        // Tổng số yêu cầu chờ phê duyệt
        $countTotal = $countLeaveRequest + $countExitRequest + $countLateEarlyRequest + $countAttendanceRequest;
    
        // Trả về kết quả tổng hợp dưới dạng JSON
        return response()->json([
            'count_leave_request' => $countLeaveRequest,
            'count_exit_request' => $countExitRequest,
            'count_late_early_request' => $countLateEarlyRequest,
            'count_attendance_request' => $countAttendanceRequest,
            'count_total' => $countTotal,
        ]);
    }


    public function updateIsRead(Request $request)
    {
                $noti = Noitification::find($request->id);
                if ($noti) {
                    $noti->update(['is_read' => $request->is_read]);
                    return response()->json([
                        'message' => 'Update successful isRead',
                    ], 200);
                } else {
                    return response()->json([
                        $noti
                    ], 200);
                }
    }
     
    
}
