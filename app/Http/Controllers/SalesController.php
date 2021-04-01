<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function getMonthlyRevenue()
    {
        $month = date('m');
        $last_month = $month - 1;
        $total_revenue = DB::table('licenses')->sum('price');
        $current_month_revenue = DB::table('licenses')->whereMonth('created_at', $month)->sum('price');
        $last_month_revenue = DB::table('licenses')->whereMonth('created_at', $last_month)->sum('price');

        $data = [];
        $months = [];
        for($i = $month; $i >= 1; $i--){
            $data[] = (int) (DB::table('licenses')->whereMonth('created_at', $i)->sum('price'));
            $months[] = date('M', mktime(0, 0, 0, (int)($i), 1));
        }

        $monthly_revenues = ["months" => $months, "data" => $data];

        if($current_month_revenue === 0){
            $percent_increase = 0;
        }
        else if($last_month_revenue === 0){
            $percent_increase = 100;
        }
        else{
            $percent_increase = round( ($current_month_revenue - $last_month_revenue)/$last_month_revenue, 2 ) || 0;
        }
        return response()->json([
            'status' => 'success',
            'revenues' => [
                'current_month_revenue' => $current_month_revenue,
                'percent_increase' => $percent_increase
            ],
            'monthly_revenues' => $monthly_revenues,
            
        ], 200);
    }

    public function getSalesStats()
    {
        $total_sales = DB::table('licenses')->count();
        $recurring_sales = DB::table('licenses', 'l')->leftJoin('plans as p', 'p.id', '=', 'l.plan_id')->where('p.type', '=', 'recurring')->count();
        $life_time_sales = DB::table('licenses', 'l')->leftJoin('plans as p', 'p.id', '=', 'l.plan_id')->where('p.type', '=', 'lifetime')->count();
        $users_count = User::count();

        $plan_revenue = DB::table('licenses')
        ->leftJoin('plans as p', 'p.id', '=', 'licenses.plan_id')
        ->selectRaw('SUM(licenses.price) as amount, p.name')
        ->groupBy('licenses.plan_id', 'p.name')
        ->get();

        $plan_sales = DB::table('licenses')
        ->leftJoin('plans as p', 'p.id', '=', 'licenses.plan_id')
        ->selectRaw('COUNT(licenses.price) as sale, p.name')
        ->groupBy('licenses.plan_id', 'p.name')
        ->get();
        
        return response()->json([
            'status' => 'success',
            'sales_stats' => [
                'total_sales' => $total_sales,
                'recurring_sales' => $recurring_sales,
                'life_time_sales' => $life_time_sales,
                'users_count' => $users_count
            ],
            'plan_stats' => [
                'plan_revenue' => $plan_revenue,
                'plan_sales' => $plan_sales
            ]
        ], 200);
    }

    public function getPlanSalesStats()
    {
        $plan_revenue = DB::table('licenses')
        ->leftJoin('plans as p', 'p.id', '=', 'licenses.plan_id')
        ->selectRaw('SUM(licenses.price) as plans, p.name')
        ->groupByRaw('licenses.plan_id')
        ->get();

        $plan_sales = DB::table('licenses')
        ->leftJoin('plans as p', 'p.id', '=', 'licenses.plan_id')
        ->selectRaw('COUNT(licenses.price) as plans, p.name')
        ->groupByRaw('licenses.plan_id')
        ->get();

        return response()->json([
            'status' => 'success',
            'plan_revenue' => $plan_revenue,
            'plan_sales' => $plan_sales
        ], 200);
    }

}
