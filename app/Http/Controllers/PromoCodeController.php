<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function __construct()
    {
        // $this->user = Auth::user();
        $this->stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    }

    public function generate_promo_codes(Request $request)
    {
        try {
            //code...
            $promoCodes = [];
            $number = $request->input('number');
            for ($i=0; $i < $number; $i++) { 
                $promoCode = new PromoCode([
                    'coupon' => $request->input('coupon')
                ]);
                
                $stripePromoCode = $this->stripe->promotionCodes->create([
                    'coupon' => $promoCode->coupon,
                  ]);
    
                  $promoCode->save();
                  $promoCodes[] = $promoCode;
            }
            return response()->json([
                'status' => 'success',
                'promoCodes' => $promoCodes
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function get_promo_codes()
    {
        try {
            //code...
            $promoCodes = PromoCode::all();
            return response()->json([
                'status' => 'success',
                'promoCodes' => $promoCodes
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }
    
    public function create_promo_code(Request $request)
    {
        try {
            //code...
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $promoCode = new PromoCode([
                'coupon' => $request->input('coupon')
            ]);
            
            $stripePromoCode = $this->stripe->promotionCodes->create([
                'coupon' => $promoCode->coupon,
              ]);

              $promoCode->save();
            return response()->json([
                'status' => 'success',
                'promoCode' => $promoCode
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function get_promo_code(Request $request)
    {
        
    }

    public function update_promo_code(Request $request)
    {
        
    }

    public function delete_promo_code($id)
    {
        try {
            //code...
            $promoCode = PromoCode::find($id);
            return response()->json([
                'status' => 'success',
                'promoCode' => $promoCode
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
