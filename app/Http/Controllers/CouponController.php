<?php

namespace App\Http\Controllers;

use  App\Models\Coupon;
use App\Models\PromoCode;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Exception;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->user = Auth::user();
        $this->stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    }

    public function get_coupons()
    {
        try {
            $coupons = Coupon::orderBy('updated_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'coupons' => $coupons
            ], 200);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function create_coupon(Request $request)
    {
        try {
            $coupon = new Coupon([
                'name' => $request->input('name'),
                'duration' => $request->input('duration'),
                'duration_in_months' => $request->input('duration_in_months'),
                'percent_off' => $request->input('percent_off'),
                'max_redemptions' => $request->input('max_redemptions')
            ]);

            $stripe_coupon = $this->stripe->coupons->create([
                'name' => $coupon->name,
                'duration' => $coupon->duration,
                'duration_in_months' => $coupon->duration_in_months,
                'percent_off' => $coupon->percent_off,
                'max_redemptions' => $coupon->max_redemptions,
            ]);

            $promoCodes = [];
            $number_promo_codes = $request->input('number_promo_codes');
            for ($i=0; $i < $number_promo_codes; $i++) { 
                $promoCode = new PromoCode([
                    'coupon' => $stripe_coupon->id
                ]);
                
                $stripePromoCode = $this->stripe->promotionCodes->create([
                    'coupon' => $promoCode->coupon,
                  ]);
    
                  $promoCode->code = $stripePromoCode->code;
                  $promoCode->save();
                  $promoCodes[] = $promoCode;
            }

            $coupon->stripe_id = $stripe_coupon->id;
            $coupon->save();
    
            return response()->json([
                'status' => 'success',
                'coupon' => $coupon
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function get_coupon($id)
    {
        try {
            $coupon = Coupon::find($id);
            if(!$coupon){
                throw new Exception("Coupon not found");
            }
            return response()->json([
                'status' => 'success',
                'coupon' => $coupon
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function update_coupon(Request $request, $id)
    {
        try {
            $coupon = Coupon::find($id);
            if(!$coupon){
                throw new Exception("Coupon not found");
            }
            $coupon->name = $request->input('name');
            $coupon->duration = $request->input('duration');
            $coupon->duration_in_months = $request->input('duration_in_months');
            $coupon->percent_off = $request->input('percent_off');
            $coupon->max_redemptions = $request->input('max_redemptions');

            $stripe_coupon = $this->stripe->coupons->update($coupon->stripe_id, [
                'name' => $coupon->name
            ]);

            $coupon->save();

            return response()->json([
                'status' => 'success',
                'coupon' => $coupon
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function delete_coupon($id)
    {
        try {
            $coupon = Coupon::find($id);
            if(!$coupon){
                throw new Exception("Coupon not found");
            }
            $stripe_coupon = $this->stripe->coupons->delete($coupon->stripe_id, []);

            $coupon->delete();

            return response()->json([
                'status' => 'success',
                'coupon' => $coupon
            ], 204);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }
}
