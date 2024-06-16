<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Validator;
use App\Http\Requests\AuthRequest;
use App\Models\User;
use App\Models\Information;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Verifytoken;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;


class AuthController extends Controller
{
    public function register(Request $request)
    {

        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            if($existingUser->role == 'manager'){
                return response()->json(['message' => 'Email đã được đăng kí với vai trò quản lý'], 400);

            }else{
                return response()->json(['message' => 'Email đã được đăng kí với vai trò nhân viên'], 400);

            }
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $infoData = [
            'user_id' =>$user->id,
            'email' =>$request->email,
            'ho_ten' =>$request->name,
            'so_dien_thoai' => '-',
            'nam_sinh' => '-',
            'gioi_tinh' => '-',
            'dia_chi' => '-',
            'ngan_hang' => '-',
            'so_tai_khoan' => '-',
            'anh_mat_truoc' => '-',
            'anh_mat_sau' => '-'
        ];

            Information::create($infoData);

            $token = JWTAuth::attempt($request->only('email', 'password'));

            if (!$token) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }


        
        return response()->json(['message' => 'User created successfully', 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if ($token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'message' => 'User created successfully',
                'token' => $token,
            ], 200);
        } else {
            return response()->json(['message' => 'Vui lòng kiểm tra lại email hoặc mật khẩu'], 401);
        }
    }

    

    public function update(Request $request)
    {
        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');
    
        if ($userId) {
            $userData = [
                // 'password' => $request->password,
                'token_device' => $request->token_device,
            ];
    
                $user = User::find($userId);
                    $user->update($userData);
                    return response()->json([
                        'message' => 'Update successful user',
                    ], 200);
        } else {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }
    }


    public function changePassword(Request $request)
    {
        $token = $request->bearerToken();
        $email = JWTAuth::getPayload($token)->get('email');

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Email hoặc mật khẩu hiện tại không đúng.'], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Đổi mật khẩu thành công.']);
    }
    
}
