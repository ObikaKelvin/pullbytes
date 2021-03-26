<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Http\Requests\LicenseRequest;


// use App\License;
use Illuminate\Support\Str;
use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use Error;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller{

    public function get_licenses(){
        try {
            //code...
            // $licenses = License::all();
            $licenses = DB::table('licenses', 'l')->select('l.id', 'l.license_number', 'p.name as plan', 'l.price', 'l.expires_at', 'l.status', 'u.name as user')->leftJoin('plans as p', 'p.id', '=', 'l.plan_id')->leftJoin('users as u', 'l.user_id', '=', 'u.id')->get();
            return response()->json([
                'status' => 'success',
                'licenses' => $licenses
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function create_license(LicenseRequest $request){

        try {
            //code...
            $active_urls = $request->input('active_urls');
            $license = new License([
                'license_number' => Str::uuid(),
                'subscription_id' => $request->input('subscription_id'),
                'plan_id' => $request->input('plan_id'),
                'user_id' => $request->input('user_id'),
                'price' => $request->input('price'),
                'number_of_urls' => $request->input('number_of_urls'),
                'active_urls' => json_encode($active_urls),
            ]);

            $user = User::find($license->user_id);
            $plan = Plan::find($license->plan_id);
            if(count($active_urls) > $plan->active_url_number){
                throw new Exception("This plan only supports up to ".$plan->active_url_number." webites");
            }
            if($plan->type === 'recurring'){
                $user->trial_ends_at = now()->addDays($request->input('trial_days'));
                $user->save();
                $license->expires_at = now()->addYears(1);
            }
            $license->save();
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([   
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'license' => $license
        ], 201);

    }

    public function get_license($id){
        try {
            //code...
            $license = DB::table('licenses', 'l')->select('l.id', 'l.license_number', 'p.name as plan', 'l.number_of_urls', 'l.active_urls', 'l.expires_at', 'l.status', 'u.name as user')->leftJoin('plans as p', 'p.id', '=', 'l.plan_id')->leftJoin('users as u', 'u.id', '=', 'l.user_id')->where('l.id', $id)->first();
            if(!$license){
                throw new Exception("License was not found");
            }
            return response()->json([
                'status' => 'success',
                'license' => $license
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json($th->getMessage(), 404);
        }
    }

    public function delete_license($id){
        try {
            //code...
            $license = License::find($id);
            if(!$license){
                throw new Exception("License was not found");
            }
            $license->delete();
            return response()->json(null, 204);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json($th->getMessage(), 404);
        }
    }

    public function update_license(Request $request, $id){
        try {
            //code...
            // $license = License::where('license_number', $license_number)->where('user_id', Auth::user()->id)->first();
            $license = License::find($id);
            $license->status = $request->status;
            $license->save();
            if(!$license){
                throw new Exception("License was not found");
            }
            return response()->json([
                'status' => 'success',
                'license' => $license
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(
                [   
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ],
                404);
        }
    }

    public function get_my_licenses(Request $request){
        try {
            //code...
            $licenses = License::where('user_id', $request->user()->id)->get();
            return response()->json($licenses, 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json($th, 404);
        }
    }

    public function get_my_license($license_number){
        try {
            //code...
            $license = License::where('license_number', $license_number)->where('user_id', Auth::user()->id)->first();
            if(!$license){
                throw new Exception("License was not found");
            }
            return response()->json([
                'status' => 'success',
                'license' => $license
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(
                [   
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ],
                404);
        }
    }

    public function update_my_license(Request $request, $license_number){
        try {
            //code...
            $license = License::where('license_number', $license_number)->where('user_id', Auth::user()->id)->first();
            $license->status = $request->input('status');
            $license->active_urls = $request->input('active_urls');
            $license->save();
            if(!$license){
                throw new Exception("License was not found");
            }
            return response()->json([
                'status' => 'success',
                'license' => $license
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(
                [   
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ],
                404);
        }
    }
}
