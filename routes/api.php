<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FacultyController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UmbrellaProgramController;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth
Route::group(['prefix' => '/auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    // Auth
    Route::group(['prefix' => '/auth'], function () {
        Route::get('/user', [AuthController::class, 'getInfo']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-pass', [AuthController::class, 'changePass']);
    });

    // Umbrella Programs
    Route::group(['prefix' => '/umbrella-program'], function () {
        Route::get('/', [UmbrellaProgramController::class, 'getUmbrella']);
    });

    // Schedule
    Route::group(['prefix' => '/'], function () {
        Route::post('/schedule', [ScheduleController::class, 'schedule_create']);
        Route::post('/schedule-save', [ScheduleController::class, 'schedule_save']);
    });

    Route::group(['prefix' => '/faculty'], function () {
        Route::get('/{faculty_id}/rooms', [RoomController::class, 'get_rooms_by_faculty']);
        Route::get('/{faculty_id}/teachers', [TeacherController::class, 'get_teachers_by_faculty']);
        Route::get('/{faculty_id}/umbrella-program', [UmbrellaProgramController::class, 'get_umbrella_program_by_faculty']);
        Route::get('/', [FacultyController::class, 'get_faculty']);
    });

    Route::get('/program', [ProgramController::class, 'getAll']);
    Route::post('/program', [ProgramController::class, 'store']);
    Route::delete('/program/{program_id}', [ProgramController::class, 'delete']);
    Route::get('/team', [TeamController::class, 'getAll']);

});

Route::post('/assignment', [UmbrellaProgramController::class, 'assignment_create']);