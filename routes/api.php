<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EmailManagementController;


// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/send-notification', [NotificationController::class, 'sendNotification']);
// });

Route::prefix('notifications')->group(function () {
    Route::get('unread', [NotificationController::class, 'getUnreadNotifications']);
    Route::put('mark-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('all', [NotificationController::class, 'viewAllNotifications']);
    Route::post('send', [NotificationController::class, 'sendNotification']);
});

Route::post('file/store', 'App\Http\Controllers\TestController@index');


Route::post('file/store', 'App\Http\Controllers\TestController@index');


Route::get('/email-managements', [EmailManagementController::class, 'index'])->name('email-managements.index');
Route::post('/email-managements/toggle-active/{id}', [EmailManagementController::class, 'toggleActive'])->name('email-managements.toggle-active');
Route::post('/email-managements', [EmailManagementController::class, 'store'])->name('email-managements.store');
