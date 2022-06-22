<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\MailChimpController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// auth
Route::post('login', [APIController::class, 'login'])->name('api.login');
Route::post('register', [APIController::class, 'register'])->name('api.register');
Route::post('reset_password', [APIController::class, 'reset_password'])->name('api.reset_password');

Route::post('project/get_project_map_street', [APIController::class, 'get_project_map_street']);

// default price
Route::post('price/get_price', [APIController::class, 'get_price']);
Route::post('price/update_uom', [APIController::class, 'update_uom']);
Route::post('price/update_price', [APIController::class, 'update_price']);

// projects
Route::post('project/get_project', [APIController::class, 'get_project']);
Route::post('project/add_project', [APIController::class, 'add_project']);
Route::post('project/update_project', [APIController::class, 'update_project']);
Route::post('project/remove_project', [APIController::class, 'remove_project']);

// project detail
Route::post('project/get_project_details', [APIController::class, 'get_project_details']);
Route::post('project/update_use_atom', [APIController::class, 'update_use_atom']);
Route::post('project/update_project_detail_item', [APIController::class, 'update_project_detail_item']);
Route::post('project/share_project_with_team_member', [APIController::class, 'share_project_with_team_member']);
Route::post('project/un_share_project_with_team_member', [APIController::class, 'un_share_project_with_team_member']);
Route::post('project/update_shared_job_edit_permission', [APIController::class, 'update_shared_job_edit_permission']);
Route::post('project/add_non_team_member', [APIController::class, 'add_non_team_member']);

// get project analysis
Route::post('project/get_analysis', [APIController::class, 'get_analysis']);
Route::post('project/update_analysis', [APIController::class, 'update_analysis']);
Route::post('project/update_analysis_detail', [APIController::class, 'update_analysis_detail']);

// get comparable sales using ATTOM API
Route::post('project/get_comparable_sales', [APIController::class, 'get_comparable_sales']);
// get cost info
Route::get('project/get_cost_info', [APIController::class, 'get_cost_info']);
Route::post('project/add_cost_info', [APIController::class, 'add_cost_info']);

// get budget
Route::post('project/get_budget', [APIController::class, 'get_budget']);
Route::post('project/update_budget', [APIController::class, 'update_budget']);

// get picture
Route::post('project/get_pictures', [APIController::class, 'get_pictures']);
Route::post('project/upload_picture', [APIController::class, 'upload_picture']);

// preference API
Route::get('preference/get_preference', [APIController::class, 'get_preference']);
Route::post('preference/update_preference', [APIController::class, 'update_preference']);
Route::post('preference/add_team_member', [APIController::class, 'add_team_member']);
Route::post('preference/remove_team_member', [APIController::class, 'remove_team_member']);

// account API
Route::get('account/get_account_info', [APIController::class, 'get_account_info']);

// contact API
Route::get('contact/get_contact', [APIController::class, 'get_contact']);

// get app info API
Route::get('about/get', [APIController::class, 'get_about_info']);

// Share QR link API
Route::post('share/share_link_email', [APIController::class, 'share_link_email']);

// payment API
Route::post('pay/pay', [APIController::class, 'pay']);
Route::post('pay/live_pay', [APIController::class, 'live_pay']);

Route::post('pay/create_customer_test', [APIController::class, 'create_customer_test']);
Route::post('pay/create_subscription_test', [APIController::class, 'create_subscription_test']);
Route::post('pay/create_subscription', [APIController::class, 'create_subscription']);

Route::post('pay/cancel_subscription_test', [APIController::class, 'cancel_subscription_test']);
Route::post('pay/cancel_subscription', [APIController::class, 'cancel_subscription']);

Route::post('pay/in_app_pay_success', [APIController::class, 'inAppPaySuccess']);

Route::post('pay/web_hook', [APIController::class, 'web_hook']);

// Mailchimp API
Route::get('mailchimp/send-mail-using-mailchimp', [MailChimpController::class, 'index']);