<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/v1/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/v1/login', 'App\Http\Controllers\AuthController@login')->middleware('api');
Route::post('/v1/register', 'App\Http\Controllers\AuthController@register')->middleware('api');
Route::post('/v1/verify_license', 'App\Http\Controllers\LicenseController@verifyLicense');



Route::group(
    [

    'middleware' => ['auth:api'],
    
    'namespace' => 'App\Http\Controllers',
    ]

, function ($router) {

    Route::resource('/v1/users', 'UserController');
    Route::post('/v1/users_file', 'UserController@update_file');
    
    Route::post('/v1/logout', 'AuthController@logout');
    Route::post('/v1/refresh', 'AuthController@refresh');

    Route::get('/v1/subscriptions', 'SubscriptionController@getSubscription');
    Route::post('/v1/checkout', 'SubscriptionController@checkout');
    Route::post('/v1/cancel_subscription', 'SubscriptionController@cancel_subscription');
    Route::post('/v1/renew_subscription', 'SubscriptionController@renew_subscription');
    Route::get('/v1/subscribe', 'SubscriptionController@getSubscribed');
    Route::post('/v1/subscribe/{planId}', 'SubscriptionController@createSubscription');
    Route::post('/v1/verifySubscription/{planId}', 'SubscriptionController@verifySubscription');

    Route::get('/v1/plans', 'PlanController@get_plans');
    Route::post('/v1/plans', 'PlanController@create_plan');
    Route::get('/v1/plans/{id}', 'PlanController@get_plan');
    Route::put('/v1/plans/{id}', 'PlanController@update_plan');
    Route::delete('/v1/plans/{id}', 'PlanController@delete_plan');

    Route::get('/v1/coupons', 'CouponController@get_coupons');
    Route::post('/v1/coupons', 'CouponController@create_coupon');
    Route::get('/v1/coupons/{id}', 'CouponController@get_coupon');
    Route::patch('/v1/coupons/{id}', 'CouponController@update_coupon');
    Route::delete('/v1/coupons/{id}', 'CouponController@delete_coupon');

    Route::post('/v1/generate_promo_codes', 'PromoCodeController@generate_promo_codes');
    Route::get('/v1/promo_codes', 'PromoCodeController@get_promo_codes');
    Route::post('/v1/promo_codes', 'PromoCodeController@create_promo_code');
    Route::get('/v1/promo_codes/{id}', 'PromoCodeController@get_promo_code');
    Route::patch('/v1/promo_codes/{id}', 'PromoCodeController@update_promo_code');
    Route::delete('/v1/promo_codes/{id}', 'PromoCodeController@delete_promo_code');

    Route::get('/v1/monthly_revenue', 'SalesController@getMonthlyRevenue');
    Route::get('/v1/sales_stats', 'SalesController@getSalesStats');
    Route::get('/v1/get_plan_sales_stats', 'SalesController@getPlanSalesStats');
    

    Route::get('/v1/licenses', 'LicenseController@get_licenses');
    Route::post('/v1/licenses', 'LicenseController@create_license');
    Route::get('/v1/licenses/{id}', 'LicenseController@get_license');
    Route::patch('/v1/licenses/{id}', 'LicenseController@update_license');
    Route::delete('/v1/licenses/{id}', 'LicenseController@delete_license');

    Route::get('/v1/tickets', 'TicketController@get_tickets');
    Route::post('/v1/tickets', 'TicketController@create_ticket');
    Route::get('/v1/tickets/{id}', 'TicketController@get_ticket');
    Route::patch('/v1/tickets/{id}', 'TicketController@update_ticket');
    Route::delete('/v1/tickets/{id}', 'TicketController@delete_ticket');

    Route::get('/v1/notifications', 'NotificationController@get_notifications');
    Route::post('/v1/notifications', 'NotificationController@create_notification');
    Route::get('/v1/notifications/{id}', 'NotificationController@get_notification');
    Route::patch('/v1/notifications/{id}', 'NotificationController@update_notification');
    Route::delete('/v1/notifications/{id}', 'NotificationController@delete_notification');

});

Route::group(
    [

    'middleware' => ['auth:api'],
    
    'prefix' => '/v1/me',
    'namespace' => 'App\Http\Controllers',
    ]

, function ($router) {

    Route::post('/', 'AuthController@me');
    Route::patch('/update_password', 'AuthController@update_password');
    Route::patch('/update_me', 'UserController@update_me');
    Route::delete('/delete_me', 'UserController@delete_me');
    
    Route::get('/tickets', 'TicketController@get_my_tickets');
    Route::post('/tickets', 'TicketController@create_my_tickets');
    Route::get('/tickets/{id}', 'TicketController@get_my_ticket');
    Route::patch('/tickets/{id}', 'TicketController@update_my_ticket');
    Route::delete('/tickets/{id}', 'TicketController@delete_my_ticket');

    Route::get('/get_intent', 'SubscriptionController@getSetupIntent');
    Route::patch('/cancel_subscription/{id}', 'SubscriptionController@cancel_subscription');
    Route::patch('/renew_subscription/{id}', 'SubscriptionController@renew_subscription');


    Route::get('/licenses', 'LicenseController@get_my_licenses');
    Route::get('/licenses/{id}', 'LicenseController@get_my_license');
    Route::patch('/licenses/{id}', 'LicenseController@update_my_license');

});