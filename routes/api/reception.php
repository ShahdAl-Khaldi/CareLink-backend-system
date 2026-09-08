<?php

use App\Http\Controllers\Api\Reception\ReceptionController;
use Illuminate\Support\Facades\Route;

// كانت بس auth:sanctum (أي حساب مسجل دخول، مش موظف استقبال تحديداً) - ضفنا
// checkRole زي باقي الأدوار

Route::middleware(['auth:sanctum', 'checkRole:reception'])->prefix('reception')->group(function () {
     Route::post('/patients', [ReceptionController::class, 'registerPatient']);
    Route::post('/register-and-book', [ReceptionController::class, 'registerAndBook']);

    Route::get('/doctor-schedule', [ReceptionController::class, 'getDoctorSchedule']);
    Route::post('/appointments', [ReceptionController::class, 'storeAppointment']);
    Route::get('/appointments/all', [ReceptionController::class, 'getAllAppointments']);
    Route::put('/patients/{id}', [ReceptionController::class, 'updatePatient']);
    Route::put('/patients/{id}/meta', [ReceptionController::class, 'updatePatientMeta']);
    Route::delete('/patients/{id}', [ReceptionController::class, 'destroyPatient']);

    //  Route::post('/appointments', [ReceptionController::class, 'createAppointment']);
    Route::put('/appointments/{id}', [ReceptionController::class, 'updateAppointment']);
    Route::delete('/appointments/{id}', [ReceptionController::class, 'cancelAppointment']);

    // أزرار "تسجيل حضور" / "إلغاء" و"تحويل للطبيب" بلوحة الاستقبال بترسل على هالمسارين تحديداً
    Route::patch('/appointments/{id}/status', [ReceptionController::class, 'updateAppointmentStatus']);
    Route::post('/appointments/{id}/transfer', [ReceptionController::class, 'transferToDoctor']);
    Route::post('/appointments/{id}/end', [ReceptionController::class, 'endVisit']);

    Route::get('/waiting-queue', [ReceptionController::class, 'getWaitingQueue']);

    Route::get('/doctors', [ReceptionController::class, 'listDoctors']);

    // تسليم/تسلّم الوردية
    Route::get('/shift-handovers', [ReceptionController::class, 'listShiftHandovers']);
    Route::post('/shift-handovers', [ReceptionController::class, 'storeShiftHandover']);
    Route::post('/shift-handovers/{id}/acknowledge', [ReceptionController::class, 'acknowledgeShiftHandover']);

});

Route::middleware(['auth:sanctum', 'checkRole:reception,admin'])->prefix('reception')->group(function () {

    Route::get('/patients', [ReceptionController::class, 'listPatients']);
});

