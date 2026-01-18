<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\IssuedItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BudgetController;

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

// Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/', function () {
    // If users are logged in, redirect to dashboard, otherwise show landing page
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return view('welcome');
})->name('landing');
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
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read_all');

    // Product & Category Management Routes
    Route::resource('categories', CategoryController::class);
    Route::get('/subcategories', [CategoryController::class, 'subcategoryIndex'])->name('subcategories.index');
    Route::get('/subcategories/create', [CategoryController::class, 'subcategoryCreate'])->name('subcategories.create');
    Route::post('/subcategories/store', [CategoryController::class, 'subcategoryStore'])->name('subcategories.store');
    Route::post('/subcategories/edit/{id}', [CategoryController::class, 'subcategoryStore'])->name('subcategories.edit');
    Route::post('/subcategories/delete/{id}', [CategoryController::class, 'subcategoryStore'])->name('subcategories.destroy');

    Route::resource('products', ProductController::class);
    Route::get('/products/by-category/{categoryId}', [App\Http\Controllers\ProductController::class, 'getByCategory'])->name('products.by_category');

    // Lot Resources
    Route::get('/lots/autocomplete', [LotController::class, 'autocomplete'])->name('lots.autocomplete');
    Route::resource('lots', LotController::class);
    Route::get('/lots/entry/index', [LotController::class, 'entryIndex'])->name('lots.entry.index');
    Route::post('/lots/{lot}/approve', [App\Http\Controllers\LotController::class, 'approve'])->name('lots.approve');
    Route::post('/lots/{lot}/reject', [App\Http\Controllers\LotController::class, 'reject'])->name('lots.reject');

    // Stock Resources
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/show/{product}', [StockController::class, 'show'])->name('stock.show');
    Route::get('/stock/check-status/{product}', [StockController::class, 'checkProductStatus'])->name('stock.check_status');

    // Requisition Resources
    Route::get('/requisitions/pending', [RequisitionController::class, 'pending'])->name('requisitions.pending');
    Route::post('/requisitions/{requisition}/approve', [RequisitionController::class, 'approve'])->name('requisitions.approve');
    Route::post('/requisitions/{requisition}/reject', [RequisitionController::class, 'reject'])->name('requisitions.reject');
    Route::resource('requisitions', RequisitionController::class);
    Route::get('/requisitions/{requisition}/details', [RequisitionController::class, 'getDetails'])->name('requisitions.get_details');
    Route::post('/requisitions/{requisition}/update-items', [RequisitionController::class, 'updateItems'])->name('requisitions.update_items');

    // Budget Resources
    Route::resource('budgets', BudgetController::class);

    // Issued Items Routes
    Route::get('/my-items', [IssuedItemController::class, 'index'])->name('issued_items.index');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/stock/current', [ReportController::class, 'currentStock'])->name('stock.current');
        Route::get('/consumption', [ReportController::class, 'consumption'])->name('consumption');
    });
    Route::get('/get-sub-categories/{categoryId}', [CategoryController::class, 'getSubCategories'])->name('get.sub_categories');
    Route::get('/get-products-by-sub-category/{subCategoryId}', [App\Http\Controllers\LotController::class, 'getBySubCategory'])->name('products.by_sub_category');
    // Requisition Print/Slip Route
    Route::get('/requisitions/{requisition}/print', [App\Http\Controllers\RequisitionController::class, 'printSlip'])->name('requisitions.print');

    Route::post('/requisitions/{requisition}/acknowledge', [App\Http\Controllers\RequisitionController::class, 'acknowledge'])->name('requisitions.acknowledge');
});

