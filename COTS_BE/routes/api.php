<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/khach-hang/check-token', [ClientController::class, 'checkToken']);
Route::post('/khach-hang/dang-nhap', [ClientController::class, 'login']);

Route::prefix('workspace')->group(function () {
    Route::get('/get-data', [WorkspaceController::class, 'getData']);
    Route::post('/add-data', [WorkspaceController::class, 'addData']);
    Route::put('/update', [WorkspaceController::class, 'update']);
    Route::post('/delete', [WorkspaceController::class, 'destroy']);
});

Route::prefix('/member')->group(function () {
    Route::get('/get-data', [MemberController::class, 'getData']);
    Route::post('/add-data', [MemberController::class, 'addData']);
    Route::post('/update', [MemberController::class, 'updateData']);
    Route::post('/delete', [MemberController::class, 'destroyData']);
    Route::post('/search', [MemberController::class, 'searchData']);
});

Route::get('/workspace-role/get-data', [MemberController::class, 'getDataWorkSpaceRole']);

Route::prefix('task')->group(function () {
    Route::get('/get-data', [TaskController::class, 'getData']);
    Route::post('/add-data', [TaskController::class, 'addData']);
    Route::put('/update', [TaskController::class, 'update']);
    Route::put('/move', [TaskController::class, 'move']);
    Route::post('/delete', [TaskController::class, 'destroy']);
});

Route::post('/board/add', [TaskController::class, 'addBoard']);
