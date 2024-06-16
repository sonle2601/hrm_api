<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TaskController\PartController;
use App\Http\Controllers\TaskController\WorkShiftController;
use App\Http\Controllers\TaskController\EmployeeController;
use App\Http\Controllers\TaskController\WorkScheduleController;
use App\Http\Controllers\TaskController\TimeClockDeviceController;
use App\Http\Controllers\TaskController\AttendanceController;
use App\Http\Controllers\TaskController\PersonalWorkController;
use App\Http\Controllers\TaskController\LeaveRequestController;
use App\Http\Controllers\TaskController\LateEarlyRequestsController;
use App\Http\Controllers\TaskController\ExitRequestsController;
use App\Http\Controllers\TaskController\SalaryBonusController;
use App\Http\Controllers\TaskController\SalaryPenaltyController;
use App\Http\Controllers\TaskController\SalaryController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\News\NewsController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/send-message', [MessageController::class, 'sendMessage']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::group(['middleware' => 'jwt.auth'], function () {
    Route::put('/login', [AuthController::class, 'update']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);


    Route::post('/information', [InformationController::class, 'information']);
    Route::put('/information', [InformationController::class, 'update']);
    Route::put('/information-personal', [InformationController::class, 'updatePersonal']);
    Route::get('/information-user', [InformationController::class, 'userInfo']);

    Route::put('/information-work', [InformationController::class, 'updateEmployee']);

    Route::put('/information-authentication', [InformationController::class, 'updateInfoAuthentication']);



    Route::get('/users', function (Request $request) {
        return $request->user();
    });

    Route::get('/check-information', [InformationController::class, 'checkInformation']);
    Route::post('/store', [StoreController::class, 'store']);
    Route::put('/store', [StoreController::class, 'update']);
    Route::get('/check-store', [StoreController::class, 'checkStore']);
    Route::get('/store', [StoreController::class, 'getStoreByManagerId']);

    Route::post('/part', [PartController::class, 'part']);
    Route::get('/part', [PartController::class, 'getPart']);
    Route::post('/update_part', [PartController::class, 'updatePart']);

    Route::post('/work-shift', [WorkShiftController::class, 'store']);
    Route::get('/work-shift', [WorkShiftController::class, 'index']);
    Route::get('/common-work-shift', [WorkShiftController::class, 'show']);
    Route::put('/work-shift', [WorkShiftController::class, 'update']);


    Route::post('/employee', [EmployeeController::class, 'store']);
    Route::put('/employee', [EmployeeController::class, 'update']);
    Route::get('/employee', [EmployeeController::class, 'index']);
    Route::get('/employee_accept', [EmployeeController::class, 'employeeAccept']);
    Route::put('/employee_reply', [EmployeeController::class, 'replyUser']);
    Route::get('/employee_invite', [EmployeeController::class, 'invite']);
    Route::get('/employee_check', [EmployeeController::class, 'checkEmployee']);
    Route::get('/employees/{id}', [EmployeeController::class, 'showById']);
    Route::get('/employee-info', [EmployeeController::class, 'employeeInfo']);


    Route::post('/work_schedule', [WorkScheduleController::class, 'store']);
    Route::get('/work_schedule/{date}', [WorkScheduleController::class, 'show']);
    Route::get('/common_work_schedule/{date}', [WorkScheduleController::class, 'showWorkSchedule']);
    Route::get('/personal_work_schedule/{date}', [WorkScheduleController::class, 'personalWorkSchedule']);



    Route::post('/time_clock_device', [TimeClockDeviceController::class, 'store']);
    Route::get('/time_clock_device', [TimeClockDeviceController::class, 'index']);
    Route::put('/time_clock_device', [TimeClockDeviceController::class, 'update']);


    
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/attendance-personal/{date}', [AttendanceController::class, 'show']);
    Route::post('/attendance-request', [AttendanceController::class, 'attendanceRequest']);
    Route::get('/attendance-request/{workScheduleId}', [AttendanceController::class, 'getAttendanceRequest']);
    Route::delete('/attendance-request/{id}', [AttendanceController::class, 'destroy']);
    Route::get('/attendance-request', [AttendanceController::class, 'showAttendanceRequest']);
    Route::put('/attendance-request', [AttendanceController::class, 'update']);
    Route::get('/attendance/{employee_id}/{date}', [AttendanceController::class, 'index']);
    Route::get('/available_employees/{work_shift_id}/{date}', [PersonalWorkController::class, 'getAvailableEmployees'])
    ->name('available_employees');
    Route::get('/check-work-schedule/{id}', [PersonalWorkController::class, 'checkWorkSchedule']);

    Route::post('/leave-request', [LeaveRequestController::class, 'store']);
    Route::get('/leave-request', [LeaveRequestController::class, 'index']);
    Route::get('/daily-report/{date}', [LeaveRequestController::class, 'dailyReport']);
    Route::put('/leave-request', [LeaveRequestController::class, 'update']);
    Route::get('/leave-request/{work_schedule_id}', [LeaveRequestController::class, 'show']);


    Route::post('/late-request', [LateEarlyRequestsController::class, 'late']);
    Route::post('/early-request', [LateEarlyRequestsController::class, 'early']);
    Route::get('/early-request/{id}', [LateEarlyRequestsController::class, 'showEarly']);
    Route::get('/late-request/{id}', [LateEarlyRequestsController::class, 'showLate']);
    Route::get('/late-early-request', [LateEarlyRequestsController::class, 'index']);
    Route::put('/late-early-request', [LateEarlyRequestsController::class, 'update']);
    Route::delete('/late-early-request/{id}', [LateEarlyRequestsController::class, 'destroy']);


    Route::post('/exit-request', [ExitRequestsController::class, 'store']);
    Route::get('/exit-request', [ExitRequestsController::class, 'index']);
    Route::get('/exit-request/{id}', [ExitRequestsController::class, 'show']);
    Route::delete('/exit-request/{id}', [ExitRequestsController::class, 'destroy']);
    Route::put('/exit-request', [ExitRequestsController::class, 'update']);

    Route::post('/salary-bonus', [SalaryBonusController::class, 'store']);
    Route::get('/salary-bonus', [SalaryBonusController::class, 'index']);
    Route::get('/salary-bonus/{employee}/{startDate}/{endDate}', [SalaryBonusController::class, 'showSalaryBonusEmployee']);
    Route::get('/salary-bonus/{startDate}/{endDate}', [SalaryBonusController::class, 'showSalaryBonusPersonal']);



    Route::post('/salary-penalty', [SalaryPenaltyController::class, 'store']);
    Route::get('/salary-penalty', [SalaryPenaltyController::class, 'index']);
    Route::get('/salary-penalty/{employee}/{startDate}/{endDate}', [SalaryPenaltyController::class, 'showSalaryPenaltyEmployee']);
    Route::get('/salary-penalty/{startDate}/{endDate}', [SalaryPenaltyController::class, 'showSalaryPenaltyPersonal']);


    Route::get('/salary/{start_date}/{end_date}/{employee_id}', [SalaryController::class, 'index']);
    Route::get('/salary-personal/{start_date}/{end_date}', [SalaryController::class, 'show']);


    Route::get('/notification', [NotificationController::class, 'index']);

    Route::put('/notification', [NotificationController::class, 'updateIsRead']);


    Route::get('/count-approve', [NotificationController::class, 'countApprove']);


    Route::delete('leave-request/{id}', [LeaveRequestController::class, 'destroy']);

    Route::post('/news', [NewsController::class, 'store']);

    Route::get('/news', [NewsController::class, 'index']);

    Route::get('/news-employee', [NewsController::class, 'showEmployee']);

    Route::put('/news', [NewsController::class, 'update']);

    Route::delete('/news/{id}', [NewsController::class, 'destroy']);


    
});
