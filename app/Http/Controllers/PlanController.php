<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Http\Requests\PlanRequest;

use App\Models\Plan;
use Illuminate\Support\Str;
use Exception;

class PlanController extends Controller
{
    public function get_plans(){
        try {
            //code...
            $plans = Plan::all();
            return response()->json([
                'status' => 'success',
                'plans' => $plans
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function create_plan(PlanRequest $request){
        try {
            //code...
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $features = explode(' ', $request->features);
            $plan = new Plan([
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'active_url_number' => $request->input('active_url_number'),
                'price' => $request->input('price'),
                'interval' => $request->input('interval'),
                'description' => $request->input('description'),
                'features' => json_encode($features),
            ]);
            
            if($plan->type === 'recurring'){
                $stripe_product = $stripe->products->create([
                'name' => $plan->name,
                'description' => $request->input('description'),
                ]);

                $stripe_price = $stripe->prices->create([
                    'unit_amount' => $plan->price * 100,
                    'currency' => 'usd',
                    'recurring' => ['interval' => $plan->interval],
                    'product' => $stripe_product->id,
                ]);

                $plan->product_id = $stripe_product->id;
                $plan->price_id = $stripe_price->id;
            }
            
            $plan->save();

            return response()->json([
                'status' => 'success',
                'plan' => $plan
            ], 201);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 400);
        }

    }

    public function get_plan($id){
        try {
            //code...
            $plan = Plan::find($id);
            if(!$plan){
                throw new Exception("plan was not found");
            }
            return response()->json([
                'status' => 'success',
                'plan' => $plan
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function update_plan($id, Request $request){
        try {
            //code...
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $plan = Plan::find($id);
            if(!$plan){
                throw new Exception("plan was not found");
            }
            // $plan = $request->all();
            $plan->name = $request->input('name');
            $plan->price = $request->input('price');
            $plan->type = $request->input('type');
            $plan->interval = $request->input('interval');
            $plan->description = $request->input('description');
            $plan->features = $request->input('features');
            $plan->active_url_number = $request->input('active_url_number');

            if($plan->type === 'recurring'){
                $stripe_product = $stripe->products->update($plan->product_id, [
                'name' => $plan->name,
                'description' => $request->input('description'),
                ]);

                

                // $stripe_price = $stripe->prices->update($plan->price_id, 
                // [
                //     'metadata' => ['unit_amount' => $plan->price * 100],
                //     'unit_amount' => $plan->price * 100,
                //     'recurring' => ['interval' => $plan->interval],
                // ]);
            
            }

            $plan->save();
            return response()->json([
                'status' => 'success',
                'plan' => $plan
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function delete_plan($id){
        try {
            //code...
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $plan = Plan::find($id);
            if(!$plan){
                throw new Exception("plan was not found");
            }
            // $stripe->prices->delete(
            //     $plan->price_id,
            //     []
            //   );
            // $stripe->products->delete(
            //     $plan->product_id,
            //     []
            //   );
            $plan->delete();
            return response()->json([
                'status' => 'success',
            ], 204);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'fail',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function get_top_plans(Request $request){
        $filter = $request->input('filter');
        $date = '';
        switch ($filter) {
            case 'week':
                # code...
                break;
            
            case 'month':
                # code...
                break;

            case 'yeah':
                # code...
                break;
            
            default:
                # code...
                break;
        }

        $top_plans = DB::table('licenses')
        ->leftJoin('plans as p', 'p.id', '=', 'licenses.plan_id')
        ->selectRaw('COUNT(licenses.price) as plans, p.name')
        ->groupByRaw('licenses.plan_id')
        ->get();

        return response()->json([
            'status' => 'success',
            'top_plans' => $top_plans
        ], 200);
    }

}
