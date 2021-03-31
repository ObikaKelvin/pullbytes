<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
     /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function guard()
    {
        return Auth::guard();
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            //code...
            $credentials = request(['email', 'password']);

            $token_validity = 24 * 60;

            $this->guard()->factory()->setTTL($token_validity);

            $token = $this->guard()->attempt($credentials);
            if (! $token) {
                throw new Exception('email/password is incorrect');
            }

            return $this->respondWithToken($token);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function register(Request $request)
    {
        // $credentials = request(['email', 'password']);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $user = User::create(array_merge($validator->validated(), 
            ['password' => bcrypt($request->password)]
        ) );
        
        return response()->json(['user' => $user], 201);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();
        if($user === null){
            return response()->json([
                'status' => 'fail',
                'message' => 'user was not found'
            ], 401);
        }
        return response()->json([
            'status' => 'success',
            'user' => $user
        ], 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_password(Request $request)
    {
        try {
            //code...
            $user = $request->user();
            if($user === null){
                throw new Exception('User was not found');
            }

            if(!Hash::check($request->input('current_password'), $user->getAuthPassword())){
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Current password does not match',
                ]);
            }

            $user->password = bcrypt($request->input('password'));
            $token = $this->guard()->refresh();
            $user->save();


            return response()->json([
                'status' => 'success',
                'token' => $token,
                'user' => $user,
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 401);
        }
    }

    public function update_pic(Request $request)
    {
        try {
            //code...
            $user = $request->user();
            if($user === null){
                throw new Exception('User was not found');
            }

            // $user->password = bcrypt($request->input('password'));
            // $token = $this->guard()->refresh();
            
            // $user->save();


            return response()->json([
                'status' => 'success',
                // 'token' => $token,
                'user' => $$request,
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 401);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    public function signup(Request $request){
        $newUser = new User([
            "name" => $request->input('name'),
            "email" => $request->input('email'),
            "password" => ($request->input('password'))
        ]);

        $newUser->save();

        return response()->json([
            // "token" => $token,
            "user"  => $newUser
        ], 201);

    }
    
}
