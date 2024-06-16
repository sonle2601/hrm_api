<?php

namespace App\Http\Controllers\TaskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WorkRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function send_invitations(Request $request){
        $token = $request->bearerToken();
    
        $manage_id = JWTAuth::getPayload($token)->get('sub');

        $storeId = Store::where('manager_id', $manage_id)->value('id');
        $userId = User::where('email', $email)->value('id');
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
