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

header('Access-Control-Allow-Origin: *');
header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );

Route::post('/v1/login', 'App\Http\Controllers\AuthController@login');
Route::post('/v1/register', 'App\Http\Controllers\AuthController@register');



Route::group(
    [

    'middleware' => 'auth:api',
    'namespace' => 'App\Http\Controllers',
    ]

, function ($router) {

    Route::resource('/v1/users', 'UserController')->middleware('admin');
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
    Route::post('/v1/plans', 'PlanController@create_plan')->middleware('admin');
    Route::get('/v1/plans/{id}', 'PlanController@get_plan')->middleware('admin');
    Route::put('/v1/plans/{id}', 'PlanController@update_plan')->middleware('admin');
    Route::delete('/v1/plans/{id}', 'PlanController@delete_plan')->middleware('admin');

    Route::get('/v1/monthly_revenue', 'SalesController@getMonthlyRevenue')->middleware('admin');
    Route::get('/v1/sales_stats', 'SalesController@getSalesStats')->middleware('admin');
    Route::get('/v1/getPlanSalesStats', 'SalesController@getPlanSalesStats')->middleware('admin');
    

    Route::get('/v1/licenses', 'LicenseController@get_licenses')->middleware('admin');
    Route::post('/v1/licenses', 'LicenseController@create_license')->middleware('admin');
    Route::get('/v1/licenses/{id}', 'LicenseController@get_license')->middleware('admin');
    Route::patch('/v1/licenses/{id}', 'LicenseController@update_license')->middleware('admin');
    Route::delete('/v1/licenses/{id}', 'LicenseController@delete_license')->middleware('admin');

    Route::get('/v1/tickets', 'TicketController@get_tickets')->middleware('admin');
    Route::post('/v1/tickets', 'TicketController@create_ticket')->middleware('admin');
    Route::get('/v1/tickets/{id}', 'TicketController@get_ticket')->middleware('admin');
    Route::patch('/v1/tickets/{id}', 'TicketController@update_ticket')->middleware('admin');
    Route::delete('/v1/tickets/{id}', 'TicketController@delete_ticket')->middleware('admin');

    Route::get('/v1/notifications', 'NotificationController@get_notifications');
    Route::post('/v1/notifications', 'NotificationController@create_notification');
    Route::get('/v1/notifications/{id}', 'NotificationController@get_notification');
    Route::patch('/v1/notifications/{id}', 'NotificationController@update_notification')->middleware('admin');
    Route::delete('/v1/notifications/{id}', 'NotificationController@delete_notification')->middleware('admin');

});

Route::group(
    [

    'middleware' => 'auth:api',
    'prefix' => '/v1/me',
    'namespace' => 'App\Http\Controllers',
    ]

, function ($router) {

    Route::post('/', 'AuthController@me');
    Route::patch('/update_password', 'AuthController@update_password');
    Route::put('/update_me', 'UserController@update_me');
    Route::delete('/delete_me', 'UserController@delete_me');
    Route::get('/tickets', 'TicketController@get_my_tickets');
    Route::post('/tickets', 'TicketController@create_my_tickets');
    Route::get('/tickets/{id}', 'TicketController@get_my_ticket');
    Route::patch('/tickets/{id}', 'TicketController@update_my_ticket');
    Route::delete('/tickets/{id}', 'TicketController@delete_my_ticket');

    Route::patch('/cancel_subscription/{id}', 'SubscriptionController@cancel_subscription');
    Route::patch('/renew_subscription/{id}', 'SubscriptionController@renew_subscription');


    Route::get('/licenses', 'LicenseController@get_my_licenses');
    Route::get('/licenses/{id}', 'LicenseController@get_my_license');
    Route::patch('/licenses/{id}', 'LicenseController@update_my_license');

});