<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\RadiologyController;
use App\Http\Controllers\Api\LabOrderController;

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'checkRole:doctor'])->prefix('doctor')->group(function () {
    Route::get('/home-stats', [DoctorController::class, 'homeStats']);

    Route::get('/profile', [DoctorController::class, 'getProfile']);
    Route::put('/profile', [DoctorController::class, 'updateProfile']);
    Route::post('/profile-picture', [DoctorController::class, 'updateProfilePicture']);

    Route::post('/profile/change-password', [DoctorController::class, 'changePassword']);

    Route::get('/appointments', [AppointmentController::class, 'getDoctorAppointments']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'showDoctorAppointment']);
    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);

    Route::delete('/appointments/{id}', [AppointmentController::class, 'cancel']);
    Route::post('/appointments/{appointment}/medical-records', [AppointmentController::class, 'storeMedicalRecord']);
    // Route::post('/appointments/{id}/lab-orders', [DoctorAppointmentController::class, 'storeLabOrder']);
    // Route::get('/appointments/{appointment}/medical-records', [AppointmentController::class, 'getMedicalRecord']);

    Route::post('/appointments/{id}/diagnosis', [AppointmentController::class, 'saveDiagnosis']);
    Route::post('/appointments/{id}/lab-orders', [AppointmentController::class, 'storeLabOrder']);
    Route::post('/appointments/{id}/imaging-orders', [AppointmentController::class, 'storeImagingOrder']);
    Route::post('/appointments/{id}/prescriptions', [AppointmentController::class, 'storePrescription']);
    Route::post('/appointments/{id}/complete', [AppointmentController::class, 'completeAppointment']);

    Route::get('/medical-records', [AppointmentController::class, 'getAllMedicalRecords']);
    Route::get('/prescriptions', [AppointmentController::class, 'doctorPrescriptions']);
    Route::get('/refill-requests', [AppointmentController::class, 'doctorRefillRequests']);
    Route::post('/refill-requests/{id}/approve', [AppointmentController::class, 'approveRefillRequest']);
    Route::post('/refill-requests/{id}/deny', [AppointmentController::class, 'denyRefillRequest']);
    // حفظ السجل الطبي مرتبطاً بالموعد
    Route::get('/patients', [AppointmentController::class, 'doctorPatients']);
    Route::get('/patients/{id}', [AppointmentController::class, 'doctorPatientDetail']);

    Route::get('/broadcasts', [DoctorController::class, 'getBroadcasts']);
    Route::post('/appointments/{appointment}/prescriptions', [AppointmentController::class, 'storePrescription']);

    Route::get('/lab-orders', [LabOrderController::class, 'doctorIndex']);
    Route::get('/lab-orders/{id}', [LabOrderController::class, 'show']);
    Route::get('/imaging-orders', [RadiologyController::class, 'doctorIndex']);
    Route::get('/imaging-orders/{id}', [RadiologyController::class, 'show']);

});
