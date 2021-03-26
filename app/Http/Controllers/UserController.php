<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function __construct()
    {
        $this->status_code = 400;
    }

    /**
     * Display a  listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        return response()->json(
            [
                'status' => 'success',
                'users' => $users
            ], 
            200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        try {
            $user = new User($request->all());
            $user->password = bcrypt($user->password);
            $user->save();
            return response()->json(
                [
                    'status' => 'success',
                    'user' => $user
                ], 
                201);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ], 
                400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = User::find($id);
            
            if(!$user){
                throw new Exception('User not found');
                ;
            }
            
            return response()->json(
                [
                    'status' => 'success',
                    'user' => $user
                ], 
            200);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ], 
                404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $status_code = 400;
        try {
            if( $request->input('password') ){
                $this->status_code = 401;
                throw new Exception("Sorry, you can not updates a user's password", 1);
            }
            $user = User::find($id);
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->role = $request->input('role');
            $user->save();
            return response()->json(
                [
                    'status' => 'success',
                    'user' => $user
                ], 
                200);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ], 
                $this->status_code);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if(!$user){
                throw new Exception('User not found');
                
            }
            $user->delete();
            return response()->json(
                [
                    'status' => 'success',
                    'user' => null
                ], 
            204);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ], 
                400);
        }
    }

    /**
     * Update the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_me(UserRequest $request)
    {
        try {
            if(!auth()->user()){
                throw new Exception('User not found');
            }

            // $validator = Validator::make($request->all(), [
            //     'name' => 'required|string|between:2,100',
            //     'email' => 'required|email|unique:users',
            //     // 'email.required' => 'email is required',
            //     // 'password' => 'required|min:6'
            // ]);

           

            $user = User::find($request->user()->id);
            $user->name = $request->input('name');
            $user->email = $request->input('email');

            if($request->input('password')){
                throw new Exception("Sorry you can not update your password with this route");
            }

            $user->save();

            return response()->json(
                [
                    'status' => 'success',
                    'user' => $user
                ], 
                200);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ], 
                404);
        }
    }

    public function update_file(Request $request)
    {
        try {
            // if(!auth()->user()){
            //     throw new Exception('User not found');
            // }

            // $validator = Validator::make($request->all(), [
            //     'name' => 'required|string|between:2,100',
            //     'email' => 'required|email|unique:users',
            //     // 'email.required' => 'email is required',
            //     // 'password' => 'required|min:6'
            // ]);

           

            // $user = User::find($request->user()->id);
            // $user->name = $request->input('name');
            // $user->email = $request->input('email');

            // if($request->input('password')){
            //     throw new Exception("Sorry you can not update your password with this route");
            // }

            // $user->save();

            $all = $request->all();
            $file = $request->file('profile');
            
            // ->storeAs('avatars', $request->user()->id);

            return response()->json(
                [
                    'status' => 'success',
                    'file' => $file,
                    'all' => $all
                ], 
                200);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ], 
                404);
        }
    }

    /**
     * Soft delete the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete_me(Request $request)
    {
        try {
            if(!$request->user()){
                throw new Exception('Please login to perform this action');
            }
            $user = User::find($request->user()->id);

            $user->delete();

            return response()->json(
                [
                    'status' => 'success',
                    'user' => null
                ], 
                204);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ], 
                404);
        }
    }

}
