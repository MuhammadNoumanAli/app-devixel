<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TruckTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/', [LoginController::class, 'showLoginForm']);
Route::post('/', [LoginController::class, 'login']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard & Analytics
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('truck-type/pie-chart', [HomeController::class, 'truckTypePieChart'])->name('TruckType.PieChartData');
    Route::get('carriers/chart-type', [HomeController::class, 'carrierChartsType'])->name('carrier.chartType');
    Route::get('carriers/pi-chart', [HomeController::class, 'carrierPieChart'])->name('carrier.pieChart');
    Route::get('dispatchers/revenue/chart-type', [HomeController::class, 'dispatchersChartsRevenueType'])->name('dispatchers.chartRevenueType');
    Route::get('dispatchers/chart-type', [HomeController::class, 'dispatchersChartsType'])->name('dispatchers.chartType');

    // Real-Time Chat (Vuexy app-chat - Protected by 'chat' permission)
    Route::middleware(['permission:chat'])->group(function () {
        Route::get('/chat/{userId?}', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat-api/messages/{userId}', [ChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('/chat-api/send', [ChatController::class, 'sendMessage'])->name('chat.send');
        Route::get('/chat-api/unread-count', [ChatController::class, 'unreadCount'])->name('chat.unread');
        Route::post('/chat-api/clear/{userId}', [ChatController::class, 'clearChat'])->name('chat.clear');
    });

    // Users
    Route::get('users/{user}/change-status', [UserController::class, 'changeStatus'])->name('users.changeStatus');
    Route::get('users/change-password/{user}', [UserController::class, 'changePasswordForm'])->name('users.changePasswordForm');
    Route::patch('users/update-password/{user}', [UserController::class, 'updatePassword'])->name('users.updatePassword');
    Route::resource('users', UserController::class);

    // Carriers
    Route::get('carriers/{carrier}/send-email', [CarrierController::class, 'sendEmails'])->name('carriers.email');
    Route::get('carriers/{carrier}/assign-to', [CarrierController::class, 'assignTo'])->name('carriers.assignTo');
    Route::post('carriers/{carrier}', [CarrierController::class, 'update'])->name('carriers.update');
    Route::resource('carriers', CarrierController::class);

    // Assigned Carriers & Open Leads Workflow
    Route::get('/assigned-carriers', [CarrierController::class, 'assignedCarriers'])
        ->name('carriers.assigned')
        ->middleware('permission:assigned-carriers-list');
    Route::get('/open-leads', [CarrierController::class, 'openLeads'])
        ->name('carriers.openLeads')
        ->middleware('permission:open-leads-list');
    Route::get('/carrier-leads/{carrier}/notes', [CarrierController::class, 'getNotes'])
        ->name('carrierLeads.getNotes')
        ->middleware('permission:open-leads-notes');
    Route::post('/carrier-leads/{carrier}/notes', [CarrierController::class, 'storeNote'])
        ->name('carrierLeads.storeNote')
        ->middleware('permission:open-leads-notes');

    // Dispatchers / Loads
    Route::get('mc-details', [DispatchController::class, 'getMCDetails'])->name('dispatchers.getMCDetails');
    Route::get('dispatchers/{dispatcher}/attach-document', [DispatchController::class, 'editAttachDocument'])->name('dispatchers.editAttachDocument');
    Route::patch('dispatchers/{dispatcher}/attach-document', [DispatchController::class, 'updateAttachDocument'])->name('dispatchers.updateAttachDocument');
    Route::post('dispatchers/{dispatcher}', [DispatchController::class, 'update'])->name('dispatchers.update');
    Route::get('dispatchers/{dispatcher}/is-cancel', [DispatchController::class, 'isCancel'])->name('dispatchers.isCancel');
    Route::resource('dispatchers', DispatchController::class);

    // Reports
    Route::get('reports/carriers', [ReportsController::class, 'carriersReport'])->name('reports.carriers');
    Route::get('reports/dispatchers', [ReportsController::class, 'dispatchersReport'])->name('reports.dispatchers');
    Route::get('reports/truck-types', [ReportsController::class, 'truckTypesReport'])->name('reports.truckTypesReport');
    Route::get('reports/carrier_downloads', [ReportsController::class, 'downloadCarrierXLS'])->name('reports.carrier_downloads');
    Route::get('reports/dispatcher_downloads', [ReportsController::class, 'downloadDispatcherXLS'])->name('reports.dispatcher_downloads');

    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/invoices-not-paid', [InvoiceController::class, 'getAllCarrierNotPaid'])->name('invoices.getAllCarrierNotPaid');
    Route::get('invoices/dispatcher-report', [InvoiceController::class, 'viewDispatcherPDFView'])->name('invoices.viewDispatcherPDFView');
    Route::get('invoices/download-pdf', [InvoiceController::class, 'downloadDispatcherPDF'])->name('invoices.downloadDispatcherPDF');
    Route::get('invoices/download-xlx', [InvoiceController::class, 'downloadDispatcherXLX'])->name('invoices.downloadDispatcherXLX');
    Route::get('invoices/{dispatcher}/change-status', [InvoiceController::class, 'changeInvoiceStatus'])->name('invoices.changeInvoiceStatus');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadInvoicePdf'])->name('invoices.downloadInvoicePdf');
    Route::get('invoices/mc/{mcNumber}/download-all-pdf', [InvoiceController::class, 'downloadMcAllInvoicesPdf'])->name('invoices.downloadMcAllInvoicesPdf');
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'storePayment'])->name('invoices.payments.store');
    Route::get('invoices/{invoice}/payments', [InvoiceController::class, 'getPayments'])->name('invoices.payments.index');
    Route::delete('invoices/payments/{payment}', [InvoiceController::class, 'destroyPayment'])->name('invoices.payments.destroy');

    // Truck Types
    Route::post('truck-types/{truckType}', [TruckTypeController::class, 'update'])->name('truck-types.update');
    Route::resource('truck-types', TruckTypeController::class)->parameters([
        'truck-types' => 'truckType',
    ]);

    // Roles & Permissions
    Route::post('/roles/update', [RolePermissionController::class, 'updateRoles'])->name('roles.updateRole');
    Route::get('/roles/permissions', [RolePermissionController::class, 'permissionIndex'])->name('roles.permissionIndex');
    Route::resource('roles', RolePermissionController::class);

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'storeOrUpdate'])->name('settings.storeOrUpdate');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // Excel export
    Route::get('/write-excel', [ExcelController::class, 'writeToExcel'])->name('writeToExcel');
});
