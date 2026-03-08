<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\API\PatientProfileController;
use App\Http\Controllers\API\MedecinController;
use App\Http\Controllers\API\ConsultationController;
use App\Http\Controllers\API\PatientBloodPressureController;
use App\Http\Controllers\API\PatientBloodSugarController;
use App\Http\Controllers\API\PatientMedicalDossierController; 
use App\Http\Controllers\API\PatientDocumentController;
use App\Http\Controllers\API\RegimeAlimentaireController;
use App\Http\Controllers\API\SubscriptionController;
use App\Http\Controllers\NotificationController;




Route::post('/patient/register', [PatientController::class, 'register']);
Route::post('/patient/login', [PatientController::class, 'login']);

// Test route - remove in production
Route::get('/test', function () {
    return 'API route is working!';
});

// Authenticated routes (require a valid token)
Route::middleware('auth:sanctum')->group(function () {
    // Patient Profile Management
    Route::get('/patient/profile', [PatientProfileController::class, 'show']);
    Route::put('/patient/profile', [PatientProfileController::class, 'update']);
    Route::post('/patient/logout', [PatientProfileController::class, 'logout']);
    Route::post('/change-password', [PatientProfileController::class, 'change']);
    Route::post('/patient/profile-picture', [PatientProfileController::class, 'uploadProfilePicture']);



    // Blood Pressure Routes
    Route::get('/blood-pressures', [PatientBloodPressureController::class, 'index']);
    Route::post('/blood-pressures', [PatientBloodPressureController::class, 'store']);
    Route::get('/blood-pressures/{bloodPressure}', [PatientBloodPressureController::class, 'show']);
    Route::put('/blood-pressures/{bloodPressure}', [PatientBloodPressureController::class, 'update']);
    Route::delete('/blood-pressures/{bloodPressure}', [PatientBloodPressureController::class, 'destroy']);

    // Blood Sugar Routes
    Route::get('/blood-sugars', [PatientBloodSugarController::class, 'index']);
    Route::post('/blood-sugars', [PatientBloodSugarController::class, 'store']);
    Route::get('/blood-sugars/{bloodSugar}', [PatientBloodSugarController::class, 'show']);
    Route::put('/blood-sugars/{bloodSugar}', [PatientBloodSugarController::class, 'update']);
    Route::delete('/blood-sugars/{bloodSugar}', [PatientBloodSugarController::class, 'destroy']);

    // Doctor Routes
    Route::get('/doctors', [MedecinController::class, 'index']);
    Route::get('/doctors/{id}', [MedecinController::class, 'show']);
            
    // Consultation Routes
    Route::get('/appointments/upcoming', [ConsultationController::class, 'getUpcomingAppointments']); 
    Route::get('/appointments/past', [ConsultationController::class, 'getPastAppointments']);
    Route::post('/appointments', [ConsultationController::class, 'store']); // Patient creates a new appointment request
    Route::get('/patient/appointments', [ConsultationController::class, 'getConfirmedUpcomingAppointments']);
    


    // MEDICAL DOSSIER ROUTES (Consolidated and Protected)
    // This route handles both creation and update based on if a dossier exists
    Route::post('/patient/medical-dossier', [PatientMedicalDossierController::class, 'storeOrUpdate']);
    // This route is for retrieving the medical dossier
    Route::get('/patient/medical-dossier', [PatientMedicalDossierController::class, 'show']);
    // Document Management Routes
   Route::post('/documents', [PatientDocumentController::class, 'store']);       
    Route::get('/documents', [PatientDocumentController::class, 'index']);        
    Route::delete('/documents/{document}', [PatientDocumentController::class, 'destroy']);



    Route::get('/patients/{patient}/regimes-alimentaires', [RegimeAlimentaireController::class, 'byPatient']);
    Route::get('/regimes-alimentaires/{id}', [RegimeAlimentaireController::class, 'show']);

     // Subscription routes
    Route::get('/services', [SubscriptionController::class, 'getServices']);

    // Get the authenticated patient's current subscription (used in ServicesScreen)
    Route::get('/patient/subscription', [SubscriptionController::class, 'getCurrentSubscription']);

    // Subscribe the patient to a service (used in ServicesScreen)
    Route::post('/patient/subscribe', [SubscriptionController::class, 'subscribe']);

    // Cancel the patient's current subscription (used in ServicesScreen)
    Route::delete('/patient/subscription', [SubscriptionController::class, 'cancel']); // DELETE is semantically appropriate for 'destroying' a subscription

  Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'getApiNotifications']); // GET /api/notifications
    Route::get('/unread/count', [NotificationController::class, 'getUnreadCount']); // GET /api/notifications/unread/count
    Route::post('/{notification}/mark-as-read', [NotificationController::class, 'markApiNotificationAsRead']); // POST /api/notifications/{id}/mark-as-read
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']); // POST /api/notifications/mark-all-as-read
});


  }
);