<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function get_notifications()
    {
        try {
            $notifications = Notification::orderBy('updated_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'notifications' => $notifications
            ], 200);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 200);
        }
    }

    public function create_notification(Request $request)
    {
        try {
            $notification = new Notification([
                'title' => $request->input('title'),
                'user_id' => $this->user->id,
                'message' => $request->input('message'),
            ]);
                
            $notification = new Notification([
                'type' => 'notification creation',
                'user_id' => $this->user->id,
                'link' => '/notifications/'.$notification->id
            ]);

            $notification->save();
            $notification->save();
    
            return response()->json([
                'status' => 'success',
                'notification' => $notification
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function get_notification($id)
    {
        try {
            $notification = Notification::find($id);

            return response()->json([
                'status' => 'success',
                'notification' => $notification
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function update_notification(Request $request, $id)
    {
        try {
            $notification = Notification::find($id);
            $notification = $request->all();

            return response()->json([
                'status' => 'success',
                'notification' => $notification
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function delete_notification($id)
    {
        try {
            $notification = Notification::find($id);
            $notification->delete();

            return response()->json([
                'status' => 'success',
                'notification' => $notification
            ], 204);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function get_my_notification($id)
    {
        try {
            $notification = Notification::find($id)->where('user_id', $this->user->id);

            return response()->json([
                'status' => 'success',
                'notification' => $notification
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
