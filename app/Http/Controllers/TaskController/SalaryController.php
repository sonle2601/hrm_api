<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Store;
use App\Models\WorkSchedule;
use App\Models\SalaryBonus;
use App\Models\SalaryPenalty;
use App\Models\Information;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\WorkShift;
use App\Models\Attendance;

class SalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($startDate,$endDate,$employeeId)
    {
        // $today = Carbon::now();
        // $startDate = Carbon::createFromDate($today)->startOfMonth();
        // $endDate = Carbon::createFromDate($today)->endOfMonth();
        if ($employeeId) {
            $employee = Employee::where('id', $employeeId)
            ->with('information')
                        ->first();
            $workSchedules = WorkSchedule::where('employee_id', $employeeId)->get();
            $salaryBonuses = SalaryBonus::where('employee_id', $employeeId)
                            ->whereBetween('created_at', [$startDate, $endDate])->get();
            $salaryPenalties = SalaryPenalty::where('employee_id', $employeeId)
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->get();

            $attendances = [];

           
            $totalMinutes = 0;
            $totalSalaryBonus = 0;
            $totalSalaryPenalty = 0;
            
            foreach ($workSchedules as $workSchedule) {
                $attendances = Attendance::where('work_schedule', $workSchedule->id)
                    ->where('status', 'approved')
                    ->whereBetween('date', [$startDate, $endDate])
                    ->with('workSchedule.workShift')
                    ->first();
            
                    if ($attendances) {
                        $checkInTimestamp = strtotime($attendances->check_in);
                        $checkOutTimestamp = strtotime($attendances->check_out);
                        
                        // Tính khoảng thời gian giữa check-out và check-in
                        $timeDifferenceInSeconds = $checkOutTimestamp - $checkInTimestamp;
                        
                        // Chuyển đổi từ giây sang phút và làm tròn lên số phút
                        $timeDifferenceInMinutes = ceil($timeDifferenceInSeconds / 60);
                        
                        $total = $timeDifferenceInMinutes - $attendances->minutes_out;
                        
                        $totalMinutes += $total;
                    }
            }

            if($salaryBonuses){
                foreach ($salaryBonuses as $salaryBonus) {
                    $totalSalaryBonus = $totalSalaryBonus+$salaryBonus->amount;
                }
            }else{
                $totalSalaryBonus = 0;
            }

            if($salaryPenalties){
                foreach ($salaryPenalties as $salaryPenalty) {
                    $totalSalaryPenalty = $totalSalaryPenalty+$salaryPenalty->amount;
                }
            }else{
                $totalSalaryPenalty = 0;
            }
            
            $totalSalary = $employee->salaries*($totalMinutes/60) + $totalSalaryBonus - $totalSalaryPenalty;
            return response()->json(
                [
                    'total_salary' => ceil($totalSalary),
                    'salary_hour' => $employee->salaries,
                    'total_minutes' => $totalMinutes,
                    'totalSalary_bonus' =>$totalSalaryBonus,
                    'totalSalary_penalty' =>$totalSalaryPenalty,
                    'employee' =>$employee
                ]

            , 200);
         } else {
                return response()->json([
                    'message' => 'Error: Employee ID is required.'
                ], 400);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,$startDate,$endDate)
    {
        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');
        $employee = Employee::where('user_id', $userId)->firstOrFail();
        $employeeId = $employee->id;
        if ($employeeId) {
            $employee = Employee::where('id', $employeeId)
            ->with('information')
                        ->first();
            $workSchedules = WorkSchedule::where('employee_id', $employeeId)->get();
            $salaryBonuses = SalaryBonus::where('employee_id', $employeeId)
                            ->whereBetween('created_at', [$startDate, $endDate])->get();
            $salaryPenalties = SalaryPenalty::where('employee_id', $employeeId)
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->get();

            $attendances = [];

           
            $totalMinutes = 0;
            $totalSalaryBonus = 0;
            $totalSalaryPenalty = 0;
            
            foreach ($workSchedules as $workSchedule) {
                $attendances = Attendance::where('work_schedule', $workSchedule->id)
                    ->where('status', 'approved')
                    ->whereBetween('date', [$startDate, $endDate])
                    ->with('workSchedule.workShift')
                    ->first();
                    if ($attendances) {
                        $checkInTimestamp = strtotime($attendances->check_in);
                        $checkOutTimestamp = strtotime($attendances->check_out);
                        
                        // Tính khoảng thời gian giữa check-out và check-in
                        $timeDifferenceInSeconds = $checkOutTimestamp - $checkInTimestamp;
                        
                        // Chuyển đổi từ giây sang phút và làm tròn lên số phút
                        $timeDifferenceInMinutes = ceil($timeDifferenceInSeconds / 60);
                        
                        $total = $timeDifferenceInMinutes - $attendances->minutes_out;
                        
                        $totalMinutes += $total;
                    }
            }

            if($salaryBonuses){
                foreach ($salaryBonuses as $salaryBonus) {
                    $totalSalaryBonus = $totalSalaryBonus+$salaryBonus->amount;
                }
            }else{
                $totalSalaryBonus = 0;
            }

            if($salaryPenalties){
                foreach ($salaryPenalties as $salaryPenalty) {
                    $totalSalaryPenalty = $totalSalaryPenalty+$salaryPenalty->amount;
                }
            }else{
                $totalSalaryPenalty = 0;
            }
            
            $totalSalary = $employee->salaries*($totalMinutes/60) + $totalSalaryBonus - $totalSalaryPenalty;
            return response()->json(
                [
                    'total_salary' => ceil($totalSalary),
                    'salary_hour' => $employee->salaries,
                    'total_minutes' => $totalMinutes,
                    'totalSalary_bonus' =>$totalSalaryBonus,
                    'totalSalary_penalty' =>$totalSalaryPenalty,
                    'employee' =>$employee
                ]

            , 200);
         } else {
                return response()->json([
                    'message' => 'Error: Employee ID is required.'
                ], 400);
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
