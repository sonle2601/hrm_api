<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Employee;
use App\Models\Store;
use App\Models\User;
use App\Models\Information;
use Illuminate\Support\Facades\Hash;



class EmployeeController extends Controller
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
            $employee = Employee::where('store_id', $storeId)->get();
            return response()->json($employee->all(), 200);
        } else {
            return response()->json([
                'message' => 'Error: Store not found',
            ], 404);
        }
    }

    public function employeeAccept (Request $request)
    {
        $token = $request->bearerToken();
        $managerId = JWTAuth::getPayload($token)->get('sub');
    
        $store = Store::where('manager_id', $managerId)->first();
    
        if ($store) {
            $storeId = $store->id;
            $employees = Employee::with('store', 'user', 'part')
                ->where('store_id', $storeId)
                ->where('employee_status', 1)
                ->get();

                foreach ($employees as $employee) {
                    // Lấy thông tin user_id của nhân viên
                    $userId = $employee->user->id;
        
                    // Lấy thông tin từ bảng Information dựa trên user_id
                    $information = Information::where('user_id', $userId)->first();
        
                    // Gán thông tin từ bảng Information vào thuộc tính mới của nhân viên
                    $employee->information = $information;
                }
                
    
            return response()->json($employees, 200);
        } else {
            return response()->json([
                'message' => 'Error: Store not found',
            ], 404);
        }
    }
    

    public function invite(Request $request)
    {
        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');
    
        $employee = Employee::with('store', 'user', 'part') // Preload dữ liệu từ các bảng liên quan
        ->where('user_id', $userId)
        ->where('invitation_status', 'waiting')
        ->first();

        if($employee){
            return response()->json($employee, 200);
        } else {
            return response()->json([
                'message' => 'Error: User not found or invitation not waiting',
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
        $email = $request->input('employee_email');
    
        // Kiểm tra xem người dùng đã tồn tại dựa trên email
        $existingUser = User::where('email', $email)->first();
    
        if ($existingUser) {
            return response()->json([
                'message' => 'Tài khoản đã được tạo trước đó',
            ], 400);
        }
    
        $birthdate = $request->input('birthdate');
        $birthdateWithoutDash = str_replace('-', '', $birthdate);
        $password = date('dmY', strtotime($birthdateWithoutDash));
    
        $user = User::create([
            'name' => $request->input('employee_name'),
            'email' => $email,
            'password' => Hash::make($password), 
            'role' => 'staff',
        ]);
    
        // Tạo bản ghi trong bảng thông tin
        $information = Information::create([
            'user_id' => $user->id,
            'email' => $email,
            'ho_ten' => $request->input('employee_name'),
            'so_dien_thoai' => $request->input('employee_phone'), 
            'nam_sinh' => $birthdate,
            'gioi_tinh' => $request->input('employee_gender'),
            'dia_chi' => $request->input('employee_address'),
            'ngan_hang' => $request->input('employee_bank'),
            'so_tai_khoan' =>$request->input('bank_number'),
            'anh_mat_truoc' =>$request->input('front_photo'),
            'anh_mat_sau' =>$request->input('back_photo'),
        ]);
    
        $token = $request->bearerToken();
        $managerId = JWTAuth::getPayload($token)->get('sub');
        
        $store = Store::where('manager_id', $managerId)->first();
        $employee = Employee::create([
            'part_id' => $request->input('part_id'),
            'store_id' => $store->id,
            'user_id' => $user->id,
            'salaries' => $request->input('salaries'),
            'start_time' => $request->input('start_time'),
            'employee_status' => 1,
        ]);
    
        return response()->json([
            'message' => 'Thêm nhân viên thành công',
            'data' => $request->all(),
        ], 201);
    }
    


    // public function replyUser(Request $request)
    // {
    //     $token = $request->bearerToken();
    
    //     $userId = JWTAuth::getPayload($token)->get('sub');

    //     $employee = Employee::where('user_id', $userId)->firstOrFail();

    //     $employee->update([
    //         'invitation_status' => $request->status ? 'accept' : 'refuse',
    //     ]);
    
    //     return response()->json([
    //         'message' => 'Invitation status updated successfully',
    //         'data' => $employee,
    //     ], 200);
    // }

    public function checkEmployee(Request $request)
{
    $token = $request->bearerToken();
    $user_id = JWTAuth::getPayload($token)->get('sub');

    $employeeStatus = Employee::where('user_id', $user_id)
        ->whereIn('invitation_status', ['waiting', 'accept', 'refuse'])
        ->value('invitation_status');

    switch ($employeeStatus) {
        case 'waiting':
        case 'accept':
        case 'refuse':
            $status = $employeeStatus;
            break;
        default:
            $status = 'unknown';
            break;
    }

    return response()->json(['employee_status' => $status]);
}


    /**
     * Display the specified resource.
     */
    public function showById($id)
    {
        $employee = Employee::where('id', $id)->with('store', 'user', 'part')->first();
    
        if ($employee) {
            $userId = $employee->user->id;
        
            $information = Information::where('user_id', $userId)->first();
            $employee->information = $information;
            return response()->json($employee, 200);
        } else {
            return response()->json([
                'message' => 'Error: Employee not found',
            ], 404);
        }
    }

    public function employeeInfo(Request $request){
        $token = $request->bearerToken();
    
        $userId = JWTAuth::getPayload($token)->get('sub');

        $employee = Employee::with('store', 'user', 'part', 'information') // Preload dữ liệu từ các bảng liên quan
        ->where('user_id', $userId)
        ->first();

        if ($employee) {
            $information = Information::where('user_id', $userId)->first();
            $employee->information = $information;
            return response()->json($employee, 200);
        } else {
            return response()->json([
                'message' => 'Error: User not found',
            ], 404);
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
                $employee = Employee::where('id',$request->id)->first();
                if ($employee) {
                    $employee->update(['employee_status' => 0]);
                    return response()->json([
                        'message' => 'Sa thải thành công',
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Sa thải thất bại',
                    ], 404);
                }
           
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

}
