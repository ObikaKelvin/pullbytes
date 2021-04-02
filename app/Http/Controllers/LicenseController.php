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

    public function verifyLicense(Request $request)
    {
    
        try {
            $user = Auth::user();
            $license_number = $request->input('license_number');
            $url = $request->input('url');
           
            $license = License::where('license_number', $license_number)->first();
            $plan = Plan::find($license->plan_id);
            if(!$license){
                throw new Exception('License is invalid');
            }
            
            $acitve_urls = json_decode($license->active_urls);

            if(!in_array($url, $acitve_urls)){
                throw new Exception('Invalid website url');
            }

            if($plan->type ===  'recurring'){
                if( !$user->subscribed() && !$user->onTrial() ){
                    throw new Exception('Sorry your license has expired, please renew it and continue enjoying premium benefits');
                }
            }

            return response()->json(
                [
                    'status' => 'success',
                    'license_number' => $license->license_number,
                    'status' => $license->status,
                    'expiry_date' => $license->expires_at,
                    'billing_cycle' => $license->billing_cycle,
                ], 
            200);

        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ], 
            200);
        }
    }

    public function get_licenses(){
        try {
            //code...
            // $licenses = License::all();
            $licenses = DB::table('licenses', 'l')->select('l.id', 'l.license_number', 'l.auto_renew', 'p.name as plan', 'p.interval as interval', 'l.price', 'l.expires_at', 'l.status', 'u.name as user')->leftJoin('plans as p', 'p.id', '=', 'l.plan_id')->leftJoin('users as u', 'l.user_id', '=', 'u.id')->where('l.deleted_at', '=', null)->orderBy('l.updated_at', 'desc')->get();
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
                'subscription_id' => null,
                'plan_id' => $request->input('plan'),
                'user_id' => $request->input('users'),
                'status' => $request->input('status'),
                'price' => 0,
                'auto_renew' => 'no',
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
                $license->expires_at = $user->trial_ends_at;
            }
            $license->save();
            return response()->json([
                'status' => 'success',
                'license' => $license
            ], 201);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([   
                'status' => 'fail',
                'message' => $license
            ], 400);
        }

    }

    public function get_license($id){
        try {
            //code...
            $license = DB::table('licenses', 'l')->select('l.id', 'l.license_number', 'l.auto_renew', 'l.billing_cycle', 'p.name as plan', 'p.interval as interval', 'l.active_urls', 'l.expires_at', 'l.price', 'l.status', 'u.name as user')->leftJoin('plans as p', 'p.id', '=', 'l.plan_id')->leftJoin('users as u', 'u.id', '=', 'l.user_id')->where('l.id', $id)->where('l.deleted_at', '=', null)->first();
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
            $licenses = DB::table('licenses', 'l')->select('l.id', 'l.license_number', 'l.auto_renew', 'p.name as plan', 'p.interval as interval', 'l.price', 'l.status')->leftJoin('plans as p', 'p.id', '=', 'l.plan_id')->where('l.user_id', Auth::user()->id)->where('l.deleted_at', '=', null)->orderBy('l.created_at', 'desc')->get();
            return response()->json([
                'status' => 'success', 
                'licenses' => $licenses
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json($th, 404);
        }
    }

    public function get_my_license($id){
        try {
            //code...
            $license = DB::table('licenses', 'l')->select('l.id', 'l.license_number', 'l.billing_cycle', 'l.auto_renew', 'p.name as plan', 'p.interval as interval', 'l.active_urls', 'l.expires_at', 'l.user_id','l.price', 'l.status')->leftJoin('plans as p', 'p.id', '=', 'l.plan_id')->where('l.id', $id)->where('l.user_id', Auth::user()->id)->where('l.deleted_at', '=', null)->first();
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

    public function update_my_license(Request $request, $id){
        try {
            //code...
            $license = License::where('id', $id)->where('user_id', Auth::user()->id)->first();
            $license->active_urls = $request->input('active_urls');
            $license->auto_renew = $request->input('auto_renew');
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
