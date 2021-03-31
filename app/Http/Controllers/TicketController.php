<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TicketController extends Controller
{

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function get_tickets()
    {
        try {
            $tickets = Ticket::orderBy('updated_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'tickets' => $tickets
            ], 200);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 200);
        }
    }

    public function create_ticket(Request $request)
    {
        try {
            $ticket = new Ticket([
                'title' => $request->input('title'),
                'user_id' => $this->user->id,
                'message' => $request->input('message'),
            ]);
                
            $notification = new Notification([
                'type' => 'ticket creation',
                'user_id' => $this->user->id,
                'link' => '/tickets/'.$ticket->id
            ]);

            $ticket->save();
            $notification->save();
    
            return response()->json([
                'status' => 'success',
                'ticket' => $ticket
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function get_ticket($id)
    {
        try {
            $ticket = Ticket::find($id);

            return response()->json([
                'status' => 'success',
                'ticket' => $ticket
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function update_ticket(Request $request, $id)
    {
        try {
            $ticket = Ticket::find($id);
            $ticket->title = $request->input('title');
            $ticket->message = $request->input('message');
            $ticket->reply = $request->input('reply');
            $ticket->status = 'solved';
            $ticket->save();

            return response()->json([
                'status' => 'success',
                'ticket' => $ticket
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function delete_ticket($id)
    {
        try {
            $ticket = Ticket::find($id);
            $ticket->delete();

            return response()->json([
                'status' => 'success',
                'ticket' => $ticket
            ], 204);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function get_my_tickets()
    {
        try {
            $tickets = Ticket::where('user_id', $this->user->id)->orderBy('updated_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'tickets' => $tickets
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function get_my_ticket($id)
    {
        try {
            $ticket = Ticket::where('id', $id)->where('user_id', $this->user->id)->first();

            return response()->json([
                'status' => 'success',
                'ticket' => $ticket
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function update_my_ticket(Request $request, $id)
    {
        try {
            $ticket = Ticket::find($id)->where('user_id', $this->user->id);

            return response()->json([
                'status' => 'success',
                'ticket' => $ticket
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
