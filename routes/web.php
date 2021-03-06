<?php

use Collective\Auth\Facades\CollectiveAuth;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a given Closure or controller and enjoy the fresh air.
|
*/

/*
|--------------------------------------------------------------------------
| Public Pages
|--------------------------------------------------------------------------
*/

Route::get('/', 'PagesController@home')->name('home');
Route::get('terms-of-service', 'PagesController@termsOfService')->name('terms-of-service');
Route::get('privacy-policy', 'PagesController@privacyPolicy')->name('privacy-policy');
Route::get('contact', 'PagesController@getContact')->name('contact');

Route::post('accept-cookie-policy', 'Ajax\\CookiePolicyController@accept')->name('ajax.accept-cookie-policy');

Route::post(
    'stripe/webhook',
    '\\App\\Http\\Controllers\\WebhookController@handleWebhook'
);

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::get('register/invite', 'Auth\\RegisterController@showRegistrationInviteForm');
Route::post('register/invite', 'Auth\\RegisterController@registerViaInvite');

Route::middleware(ProtectAgainstSpam::class)->group(function () {
    CollectiveAuth::routes([
        'login' => true,
        'logout' => true,
        'register' => true,
        'reset' => true,
        'confirm' => true,
        'verify' => true,
    ]);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth', 'activity')->group(function () {
    Route::post('users/return-switch', 'Admin\\UserController@switchBack')->name('users.return-switch');

    Route::middleware('verified')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('dashboard', 'DashboardController@get')->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        Route::prefix('user')->namespace('User')->group(function () {
            Route::get('settings', 'SettingsController@index')->name('user.settings');
            Route::delete('destroy', 'DestroyController@destroy')->name('user.destroy');
            Route::put('settings', 'SettingsController@update')->name('user.update');
            Route::delete('avatar', 'SettingsController@destroyAvatar')->name('user.destroy.avatar');

            Route::get('security', 'SecurityController@index')->name('user.security');
            Route::put('security', 'SecurityController@update')->name('user.security.update');

            Route::get('api-tokens', 'ApiTokenController@index')->name('user.api-tokens');

            Route::prefix('billing')->group(function () {
                Route::middleware('has-subscription')->group(function () {
                    Route::post('update', 'BillingController@update')->name('user.billing.update');
                    Route::post('update', 'BillingController@update')->name('user.billing.update');
                    Route::post('update', 'BillingController@update')->name('user.billing.update');
                    Route::delete('cancel', 'BillingController@cancel')->name('user.billing.cancel');
                });
            });

            Route::prefix('notifications')->group(function () {
                Route::get('/', 'NotificationsController@index')->name('user.notifications');
                Route::post('{uuid}/read', 'NotificationsController@read')->name('user.notifications.read');
                Route::delete('{uuid}/delete', 'NotificationsController@delete')->name('user.notifications.destroy');
                Route::delete('clear', 'NotificationsController@deleteAll')->name('user.notifications.clear');
            });

            Route::prefix('invites')->group(function () {
                Route::get('/', 'InvitesController@index')->name('user.invites');
                Route::post('{invite}/accept', 'InvitesController@accept')->name('user.invites.accept');
                Route::post('{invite}/reject', 'InvitesController@reject')->name('user.invites.reject');
            });

            Route::prefix('billing')->group(function () {
                Route::get('subscribe', 'BillingController@subscribe')->name('user.billing');
                Route::get('renew', 'BillingController@renewSubscription')->name('user.billing.renew');
                Route::get('details', 'BillingController@getSubscription')->name('user.billing.details');
                Route::group(['gateway' => 'subscribed'], function () {
                    Route::get('payment-method', 'BillingController@paymentMethod')->name('user.billing.payment-method');
                    Route::get('change-plan', 'BillingController@getChangePlan')->name('user.billing.change-plan');
                    Route::post('swap-plan', 'BillingController@swapPlan')->name('user.billing.swap-plan');
                    Route::post('cancellation', 'BillingController@cancelSubscription')->name('user.subscription.cancel');
                    Route::get('invoices', 'BillingController@getInvoices')->name('user.billing.invoices');
                    Route::get('invoice/{id}', 'BillingController@getInvoiceById')->name('user.billing.invoice');
                    Route::get('coupon', 'BillingController@getCoupon')->name('user.billing.coupons');
                    Route::post('apply-coupon', 'BillingController@applyCoupon')->name('user.billing.apply-coupon');
                });
            });
        });

        Route::post('invites/{invite}/resend', 'InvitesController@resend')->name('invite.resend');
        Route::post('invites/{invite}/revoke', 'InvitesController@revoke')->name('invite.revoke');

        Route::prefix('teams')->group(function () {
            Route::get('/', 'TeamsController@index')->name('teams');
            Route::post('/', 'TeamsController@store')->name('teams.store');
            Route::get('create', 'TeamsController@create')->name('teams.create');
            Route::get('{team}/edit', 'TeamsController@edit')->name('teams.edit');
            Route::get('{team}/members', 'TeamsController@members')->name('teams.members');
            Route::delete('{team}/delete', 'TeamsController@destroy')->name('teams.destroy');
            Route::put('{team}/update', 'TeamsController@update')->name('teams.update');
            Route::delete('avatar', 'TeamsController@destroyAvatar')->name('team.destroy.avatar');

            Route::get('{team}', 'TeamMembersController@show')->name('teams.show');
            Route::post('{team}/leave', 'TeamMembersController@leave')->name('teams.leave');
            Route::post('{team}/invite', 'TeamMembersController@inviteMember')->name('teams.members.invite');
            Route::delete('{team}/remove/{member}', 'TeamMembersController@removeMember')->name('teams.members.remove');
            Route::get('{team}/edit/{member}', 'TeamMembersController@editMember')->name('teams.members.edit');
            Route::put('{team}/update/{member}', 'TeamMembersController@updateMember')->name('teams.members.update');
        });

        /*
        |--------------------------------------------------------------------------
        | Ajax calls (using normal auth)
        |--------------------------------------------------------------------------
        */

        Route::prefix('ajax')->namespace('Ajax')->group(function () {
            Route::get('tokens', 'ApiTokenController@index')->name('ajax.tokens');
            Route::post('token', 'ApiTokenController@create')->name('ajax.create-token');
            Route::delete('token/{token}/destroy', 'ApiTokenController@destroy')->name('ajax.destroy-token');

            Route::get('notifications-count', 'NotificationsController@count')->name('ajax.notifications-count');

            Route::post('subscribe', 'BillingController@createSubscription')
                ->name('ajax.billing.subscription.create');
            Route::post('payment-method', 'BillingController@updatePaymentMethod')
                ->name('ajax.billing.subscription.payment-method');

            Route::post('file-upload', 'FileUploadController@upload')->name('ajax.files-upload');
        });

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        Route::prefix('admin')->namespace('Admin')->middleware(['roles:admin', 'password.confirm'])->group(function () {
            Route::get('dashboard', 'DashboardController@index')->name('admin.dashboard');

            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */
            Route::get('users/search', 'UserController@search')->name('admin.users.search');
            Route::get('users/invite', 'UserController@getInvite')->name('admin.users.invite');
            Route::post('users/invite', 'UserController@postInvite')->name('admin.users.send-invite');
            Route::post('users/switch/{user}', 'UserController@switchToUser')->name('admin.users.switch');

            Route::resource('users', 'UserController', ['as' => 'admin']);

            /*
            |--------------------------------------------------------------------------
            | Roles
            |--------------------------------------------------------------------------
            */
            Route::resource('roles', 'RoleController', ['as' => 'admin']);
        });
    });
});
