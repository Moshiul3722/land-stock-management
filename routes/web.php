<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/home', [HomeController::class, 'index'])->name('home');


// Route::get('generator_builder', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@builder')->name('io_generator_builder');

// Route::get('field_template', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@fieldTemplate')->name('io_field_template');

// Route::get('relation_field_template', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@relationFieldTemplate')->name('io_relation_field_template');

// Route::post('generator_builder/generate', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@generate')->name('io_generator_builder_generate');

// Route::post('generator_builder/rollback', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@rollback')->name('io_generator_builder_rollback');

// Route::post(
//     'generator_builder/generate-from-file',
//     '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@generateFromFile'
// )->name('io_generator_builder_generate_from_file');

Route::middleware(['auth', 'permission'])->group(function () {

    Route::resource('cnfModules', App\Http\Controllers\CnfModuleController::class);
    Route::resource('cnfSubmodules', App\Http\Controllers\CnfSubmoduleController::class);
    Route::resource('cnfMenus', App\Http\Controllers\CnfMenuController::class);
    Route::resource('cnfPermissions', App\Http\Controllers\CnfPermissionController::class);
    Route::resource('cnfRoles', App\Http\Controllers\CnfRoleController::class);

    // Role Service Routes START
    Route::post('/getallroles', 'App\Http\Controllers\ServiceControllers\RoleServiceController@getAllRoles')->name('getAllRoles');
    Route::post('/getallusersbyrole', 'App\Http\Controllers\ServiceControllers\RoleServiceController@getAllUsersByRole')->name('getallusersbyrole');
    Route::post('/getallusersbyroleblade', 'App\Http\Controllers\ServiceControllers\RoleServiceController@getAllUsersByRoleBlade')->name('getallusersbyroleblade');
    // Role Service Routes END

    // Menu Permission Service START
    Route::post('/getallmenusbyrole', 'App\Http\Controllers\ServiceControllers\PermissionServiceController@getAllMenusByRole')->name('getallmenusbyrole');
    Route::post('/savepermission', 'App\Http\Controllers\ServiceControllers\PermissionServiceController@savePermission')->name('savepermission');
    // Menu Permission Service END

    // User Service Routes START
    Route::post('/getallusertypes', 'App\Http\Controllers\ServiceControllers\UserServiceController@getAllUserTypes')->name('getallusertypes');
    // User Service Routes END

    // User Module Routes START
    Route::get('/users', 'App\Http\Controllers\UserController@index')->name('users.index');
    Route::get('/createusers', 'App\Http\Controllers\UserController@create')->name('users.create');
    Route::post('/storeusers', 'App\Http\Controllers\UserController@store')->name('users.store');
    Route::get('/user/{id}/edit', 'App\Http\Controllers\UserController@edit')->name('users.edit');
    Route::put('/users/{user}', 'App\Http\Controllers\UserController@update')->name('users.update');
    // User Module Routes END

    Route::resource('years', App\Http\Controllers\YearController::class);


    // Localization Route
    Route::get('change-language/{locale}', 'App\Http\Controllers\LocalizationController@changeLanguage')->name('change.language');

    //NotificationService
    Route::prefix('notifications')->group(function () {
        Route::get('unread', [NotificationController::class, 'getUnreadNotifications']);
        Route::put('mark-as-read', [NotificationController::class, 'markAllAsRead']);
        Route::get('all', [NotificationController::class, 'viewAllNotifications']);
        Route::post('send', [NotificationController::class, 'sendNotification']);
    });

    // Product & Category Management Routes
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
});
