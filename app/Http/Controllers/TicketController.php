<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TicketController extends Controller
{

    public function get_tickets()
    {
        try {
            $tickets = Ticket::all();

            return response()->json([
                'status' => 'success',
                'tickets' => $tickets
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function create_ticket(Request $request)
    {
        try {
            $ticket = new Ticket([
                'title' => $request->input('title'),
                'user_id' => Auth::user()->id,
                'message' => $request->input('message'),
            ]);
    
            $ticket->save();
    
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
            //throw $th;
        }
    }

    public function update_ticket(Request $request, $id)
    {
        try {
            $ticket = Ticket::find($id);
            $ticket = $request->all();

            return response()->json([
                'status' => 'success',
                'ticket' => $ticket
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
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
}
