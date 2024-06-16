<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $message = $request->message;

        broadcast(new NewMessageEvent($message))->toOthers();

        return response()->json(['status' => 'Message sent']);
    }
}
