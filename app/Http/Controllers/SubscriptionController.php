<?php

namespace App\Http\Controllers;

use Exception;
use Laravel\Cashier\Subscription as Subscription;
use App\Models\Plan;
use App\Models\License;
use App\Models\User;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;

class SubscriptionController extends Controller
{  
    public function getSubscription()
    {
        $user = Auth::user();
        $subscriptions = Subscription::all();
        if($user->subscribed()){
            return response()->json(
                [
                    'status' => 'success',
                    'message' => "subscribed"
                ], 
            200);
        }

        return response()->json(
            [
                'status' => 'success',
                'subscriptions' => $subscriptions
            ], 
        200);
    }

    public function createSubscription(Request $request, $planId)
    {
    
        try {
            $user = Auth::user();
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $plan = Plan::find($planId);
                    
            if(!$user->stripe_id){
                $stripeCustomer = $user->createAsStripeCustomer();
            }

            $stripeCustomer = Cashier::findBillable($user->stripe_id);
            
            $card_token = $stripe->tokens->create([
                'card' => $request->card,
            ]);

            $customer_card = $stripe->customers->createSource(
                $user->stripe_id,
                ['source' => $card_token]
            );
            
            $intent = $user->createSetupIntent(
                [ 'customer' => $user->stripe_id ]
            );

            $confirmed_intent = $stripe->setupIntents->confirm(
                $intent->id,
                ['payment_method' => $customer_card]
            );

            $payment_method = $confirmed_intent->payment_method;


            // if($user->subscribed('5 sites classic')){
            //     throw new Exception('You are already subscribed to this plan');
            // }

            // if($user->hasDefaultPaymentMethod()){
                $user->newSubscription(
                    '5 sites classic', 'price_1IQGfLHxFZiZPKLwIOh7AGSw'
                )->create($payment_method);
            // }

            // $charge = $stripeCustomer->charge(100, $payment_method);
            
            // $products = $stripe->products->all(['product' => 'prod_J2JGBNPLqi8MUb']);
            // $prices = $stripe->prices->all(['product' => 'prod_J2JGBNPLqi8MUb']);

            return response()->json(
                [
                    'status' => 'success',
                    // 'products'=> $products,
                    // 'prices'=> $prices
                    // 'messag' => $customer_card,
                    'message' => $payment_method
                ], 
            200);

        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => 'fail',
                    // 'message' => "Sorry, can not process payment now",
                    'message' => $th->getMessage(),
                    // 'products'=> $stripe->products->retrieve('prod_J2JGBNPLqi8MUb')
                    // 'prices'=> $stripe->prices->all(['product' => 'prod_J2JGBNPLqi8MUb'])['data'][1]


                ], 
            200);
        }
    }

    public function checkout(Request $request)
    {
    
        try {
            $user = Auth::user();
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $plan = Plan::find($request->input('planId'));
                    
            if(!$user->stripe_id){
                $stripeCustomer = $user->createAsStripeCustomer();
            }

            $stripeCustomer = Cashier::findBillable($user->stripe_id);

            $license = new License([
                'license_number' => Str::uuid(),
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'price' => $plan->price,
            ]);

            if($plan->type === 'lifetime'){
                $charge = $stripeCustomer->charge(($plan->price * 100), $request->input('payment_id'));
            }else{
                if($stripeCustomer->subscribed($plan->name)){
                    throw new Exception('You are already subscribed to this plan');
                }
                $subscription = $stripeCustomer->newSubscription(
                    $plan->name, $plan->price_id
                )->create($request->input('payment_id'));
                $license->subscription_id = $subscription->id;
                $license->billing_cycle = now()->addDays(365);
            }

            $license->save();

            return response()->json(
                [
                    'status' => 'success',
                    'checkout' => true
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

    public function cancel_subscription( $id)
    {
        try {
            $user = Auth::user();
            // $license = License::where('license_number', $request->input('license_number'))->first();
            $license = License::where('id', $id)->where('user_id', $user->id)->first();
            $plan = Plan::find($license->plan_id);
            $subscription = $user->subscription($plan->name)->cancel();
            $license->expires_at = $subscription->ends_at;
            $license->auto_renew = 'no';
            $license->billing_cycle = null;
            $license->save();
            return response()->json(
                [
                    'status' => 'success',
                    'subscription' => $subscription,
                    'license' => $license
                ], 
            200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(
                [
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ], 
            200);
        }
    }

    public function renew_subscription($id)
    {
        try {
            $user = Auth::user();
            $license = License::where('id', $id)->where('user_id', $user->id)->first();
            // $license = License::where('license_number', $request->input('license_number'))->first();
            $plan = Plan::find($license->plan_id);
            $subscription = $user->subscription($plan->name);
            $license->expires_at = null;
            $license->auto_renew = 'yes';
            $license->billing_cycle = $subscription->ends_at;
            $subscription = $subscription->resume();
            $license->save();
            return response()->json(
                [
                    'status' => 'success',
                    'subscription' => $subscription
                ], 
            200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(
                [
                    'status' => 'fail',
                    'message' => $th->getMessage()
                ], 
            200);
        }
        
    }

    public function verifySubscription(Request $request, $license_number)
    {
    
        try {
            $user = Auth::user();
            $url = $request->input('url');
           
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
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
                    'message' => 'verified'
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
}
