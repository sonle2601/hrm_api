<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Information;
use App\Models\Employee;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Image;

class InformationController extends Controller
{
    public function checkInformation(Request $request)
    {
        $token = $request->bearerToken();
    
        $user_id = JWTAuth::getPayload($token)->get('sub');

        $informationExists = Information::where('user_id', $user_id)->exists();

        return response()->json(['information_exists' => $informationExists]);
    }

    public function information(Request $request){
        // Lấy JWT từ header của yêu cầu
        $token = $request->bearerToken();
    
        // Giải mã JWT và lấy thông tin user_id từ payload
        $user_id = JWTAuth::getPayload($token)->get('sub');
    
        // Tiếp tục thêm thông tin với user_id được trích xuất từ JWT
        $informationData = [
            'user_id' => $user_id,
            'email' => $request->email,
            'ho_ten' => $request->ho_ten,
            'so_cmnd' => $request->so_cmnd,
            'so_dien_thoai' => $request->so_dien_thoai,
            'nam_sinh'=> $request->nam_sinh,
            'gioi_tinh' => $request->gioi_tinh,
            'dia_chi'=>$request->dia_chi,
            'ngan_hang' =>$request->ngan_hang,
            'so_tai_khoan'=>$request->so_tai_khoan,
            'anh_mat_truoc'=> $request->anh_mat_truoc,
            'anh_mat_sau'=>$request->anh_mat_sau,
        ];
        
        if ($request->has('anh_ca_nhan')) {
            $informationData['anh_ca_nhan'] = $request->anh_ca_nhan;
        }
        
        $information = Information::create($informationData);
    
        // Return response nếu cần
        return response()->json(
            [
                'message' => 'Successfully updated',
        ], 
            201
        );
    }


    public function update(Request $request)
    {
        // $token = $request->bearerToken();
        // $manage_id = JWTAuth::getPayload($token)->get('sub');
    
        // $store = Store::where('manager_id', $manage_id)->first();
   
            $infoData = [
                'ho_ten' => $request->ho_ten,
                'so_dien_thoai' => $request->so_dien_thoai,
                'nam_sinh' => $request->nam_sinh,
                'gioi_tinh' => $request->gioi_tinh,
                'ngan_hang' => $request->ngan_hang,
                'so_tai_khoan' => $request->so_tai_khoan,
                'dia_chi' =>$request->dia_chi,
            ];
    
            if ($request->has('id')) {
                $info = Information::find($request->id);
                if ($info) {
                    // Chỉ cập nhật các trường có dữ liệu mới được gửi
                    $info->update($infoData);
    
                    return response()->json([
                        'message' => 'Update successful info',
                        'data' => $infoData
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Info not found',
                    ], 404);
                }
            } 
    }

    public function updateEmployee(Request $request) {
        $infoEmployee = [
            'part_id ' => $request->part_id ,
            'salaries' => $request->salaries,
        ];

        if ($request->has('id')) {
            $employee = Employee::find($request->id);
            if ($employee) {
                $employee->update($infoEmployee);

                return response()->json([
                    'message' => 'Cập nhật nhân viên thành công',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Employee not found',
                ], 404);
            }
        }
    }


    public function updateInfoAuthentication(Request $request)
    {
        // $token = $request->bearerToken();
        // $manage_id = JWTAuth::getPayload($token)->get('sub');
    
        // $store = Store::where('manager_id', $manage_id)->first();
   
            $infoData = [
                'anh_mat_truoc' => $request->anh_mat_truoc,
                'anh_mat_sau' =>$request->anh_mat_sau,
            ];
    
            if ($request->has('id')) {
                $info = Information::find($request->id);
                if ($info) {
                    $info->update($infoData);
    
                    return response()->json([
                        'message' => 'Cập nhập thông tin xác thực thành công',
                        'data' => $infoData
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Info not found',
                    ], 404);
                }
            } 
    }
    


    public function updatePersonal(Request $request)
    {
        // $token = $request->bearerToken();
        // $userId = JWTAuth::getPayload($token)->get('sub');
    
   
            $infoData = [
                'so_dien_thoai' => $request->so_dien_thoai,
                'nam_sinh' => $request->nam_sinh,
                'gioi_tinh' => $request->gioi_tinh,
                'ngan_hang' => $request->ngan_hang,
                'so_tai_khoan' => $request->so_tai_khoan,
                'dia_chi' =>$request->dia_chi
            ];

    
            if ($request->has('id')) {

                $info = Information::find($request->id);
                if ($info) {
                    $info->update($infoData);
                    return response()->json([
                        'message' => 'Cập nhật thông tin thành công',
                        'data' => $infoData
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Cập nhật thất bại',
                    ], 400);
                }
            } 
    }


    public function userInfo(Request $request){
        $token = $request->bearerToken();
    
        $userId = JWTAuth::getPayload($token)->get('sub');

        $user = User::with('information') 
        ->where('id', $userId)
        ->first();

        if ($user) {
            // $information = Information::where('user_id', $userId)->first();
            // $user->information = $information;
            return response()->json($user, 200);
        } else {
            return response()->json($user, 200);

        }
    }


}
