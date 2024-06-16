<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\News;
use App\Models\Store;
use App\Models\Employee;
use App\Models\Noitification;


class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $token = $request->bearerToken();
        $managerId = JWTAuth::getPayload($token)->get('sub');

        $store = Store::where('manager_id', $managerId)->first();

        $news = News::where('store_id', $store->id)
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json(
            $news
        , 200);

    }

    public function showEmployee(Request $request)
    {
        $token = $request->bearerToken();
        $userId = JWTAuth::getPayload($token)->get('sub');

        $employee = Employee::where('user_id', $userId)->first();

        $news = News::where('store_id', $employee->store_id)
        ->orderBy('created_at', 'desc')
        ->get();
    

        return response()->json(
            $news
        , 200);

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
        $managerId = JWTAuth::getPayload($token)->get('sub');

        $store = Store::where('manager_id', $managerId)->first();
        
        $data = [
            'title' => $request->title,
            'content' => $request->content,
            'image' => $request->image,
            'store_id' => $store->id    
        ];

        $result = News::create($data);

            return response()->json([
                'message' => 'Đã tạo tin thành công!',
                'data' => $data,
            ], 200);

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
        $managerId = JWTAuth::getPayload($token)->get('sub');

        $store = Store::where('manager_id', $managerId)->first();
        
        $data = [
            'title' => $request->title,
            'content' => $request->content,
            'image' => $request->image,
            'store_id' => $store->id    
        ];

        if ($request->has('id')) {
            $news = News::find($request->id);
            if ($news) {
                $news->update($data);
                
                return response()->json([
                    'message' => 'Cập nhật tin tức thành công',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Không tìm thấy tin tức cần cập nhật',
                ], 404);
            }
        }

            return response()->json([
                'message' => 'Đã tạo tin thành công!',
                'data' => $data,
            ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $news = News::findOrFail($id);
            // $noti = Noitification::where('type', 'exit_requests')
            // ->where('reference_id', $attendanceRequest->id);
            $news->delete();
            // $noti->delete();
            return response()->json(['message' => 'Đã xóa tin tức'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Xóa tin tức không thành công'], 500);
        }
    }
}
