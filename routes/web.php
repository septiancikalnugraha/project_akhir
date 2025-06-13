<?php

// Auth Routes
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// Dashboard Routes
$router->get('/dashboard', 'DashboardController@index');

// Customer Routes
$router->get('/customers', 'CustomerController@index');
$router->get('/customers/create', 'CustomerController@create');
$router->post('/customers/store', 'CustomerController@store');
$router->get('/customers/{id}/edit', 'CustomerController@edit');
$router->post('/customers/{id}/update', 'CustomerController@update');
$router->post('/customers/{id}/delete', 'CustomerController@delete');

// Deposit Routes
$router->get('/deposits', 'DepositController@index');
$router->get('/deposits/create', 'DepositController@create');
$router->post('/deposits/store', 'DepositController@store');
$router->get('/deposits/{id}/edit', 'DepositController@edit');
$router->post('/deposits/{id}/update', 'DepositController@update');
$router->post('/deposits/{id}/delete', 'DepositController@delete');

// Loan Routes
$router->get('/loans', 'LoanController@index');
$router->get('/loans/create', 'LoanController@create');
$router->post('/loans/store', 'LoanController@store');
$router->get('/loans/{id}/edit', 'LoanController@edit');
$router->post('/loans/{id}/update', 'LoanController@update');
$router->post('/loans/{id}/delete', 'LoanController@delete');

// Notification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll'])->name('notifications.delete-all');
});

// Backup routes
Route::middleware(['auth'])->group(function () {
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::get('/backups/create', [BackupController::class, 'create'])->name('backups.create');
    Route::get('/backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
    Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
    Route::get('/backups/{filename}/delete', [BackupController::class, 'delete'])->name('backups.delete');
    Route::get('/backups/cleanup', [BackupController::class, 'cleanup'])->name('backups.cleanup');
});

// Role and Permission routes
Route::middleware(['auth', 'permission:view_roles'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:create_roles');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:create_roles');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:edit_roles');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:edit_roles');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:delete_roles');
});

Route::middleware(['auth', 'permission:view_permissions'])->group(function () {
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create')->middleware('permission:create_permissions');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store')->middleware('permission:create_permissions');
    Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit')->middleware('permission:edit_permissions');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update')->middleware('permission:edit_permissions');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('permission:delete_permissions');
}); 