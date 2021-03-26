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

Route::post('/v1/login', 'App\Http\Controllers\AuthController@login');
Route::post('/v1/register', 'App\Http\Controllers\AuthController@register');

Route::put('/v1/update_password', 'App\Http\Controllers\AuthController@update_password');
Route::put('/v1/update_me', 'App\Http\Controllers\UserController@update_me');
Route::delete('/v1/delete_me', 'App\Http\Controllers\UserController@delete_me');


Route::group(
    [

    // 'middleware' => 'auth:api',
    'namespace' => 'App\Http\Controllers',
    ]

, function ($router) {

    Route::resource('/v1/users', 'UserController')->middleware('admin');
    Route::post('/v1/users_file', 'UserController@update_file');
    
    Route::post('/v1/logout', 'AuthController@logout');
    Route::post('/v1/refresh', 'AuthController@refresh');

    Route::get('/v1/subscriptions', 'SubscriptionController@getSubscription');
    Route::get('/v1/subscribe', 'SubscriptionController@getSubscribed');
    Route::post('/v1/subscribe/{planId}', 'SubscriptionController@createSubscription');
    Route::post('/v1/verifySubscription/{planId}', 'SubscriptionController@verifySubscription');


    Route::get('/v1/plans', 'PlanController@get_plans')->middleware('admin');
    Route::post('/v1/plans', 'PlanController@create_plan')->middleware('admin');
    Route::get('/v1/plans/{id}', 'PlanController@get_plan')->middleware('admin');
    Route::put('/v1/plans/{id}', 'PlanController@update_plan')->middleware('admin');
    Route::delete('/v1/plans/{id}', 'PlanController@delete_plan')->middleware('admin');

    Route::get('/v1/monthly_revenue', 'SalesController@getMonthlyRevenue')->middleware('admin');
    Route::get('/v1/sales_stats', 'SalesController@getSalesStats')->middleware('admin');
    Route::get('/v1/getPlanSalesStats', 'SalesController@getPlanSalesStats')->middleware('admin');
    

    Route::get('/v1/licenses', 'LicenseController@get_licenses')->middleware('admin');
    Route::post('/v1/licenses', 'LicenseController@create_license')->middleware('admin');
    Route::get('/v1/licenses/{license_number}', 'LicenseController@get_license')->middleware('admin');
    Route::put('/v1/licenses/{license_number}', 'LicenseController@update_license')->middleware('admin');
    Route::delete('/v1/licenses/{license_number}', 'LicenseController@delete_license')->middleware('admin');

});

Route::group(
    [

    'middleware' => 'auth:api',
    'prefix' => '/v1/me',
    'namespace' => 'App\Http\Controllers',
    ]

, function ($router) {

    Route::post('/', 'AuthController@me');

    Route::get('/licenses', 'LicenseController@get_my_licenses');
    Route::get('/licenses/{license_number}', 'LicenseController@get_my_license');
    Route::put('licenses/{license_number}', 'LicenseController@update_my_license');

});