<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\VerificationCodeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController; // Assuming this is your AdminController
use App\Http\Controllers\DashboardController; // Assuming this is your DashboardController
use App\Http\Controllers\ConsultationController; // Assuming this is your ConsultationController
use App\Http\Controllers\NotificationController; // Assuming this is your NotificationController
use App\Http\Controllers\PatientController; // Assuming this is your PatientController
use App\Http\Controllers\ProfileController; // Assuming this is your ProfileController
use App\Http\Middleware\EnsureEmailIsVerifiedCustom; // Custom middleware for email verification
use Illuminate\Support\Facades\Auth; // For authentication checks
use Illuminate\Http\Request; // For handling requests
use App\Http\Controllers\RegimeAlimentaireController; // Assuming this is your RegimeAlimentaireController
use App\Http\Controllers\DownloadController; // Assuming this is your DownloadController
use App\Http\Controllers\DocumentController; // Assuming this is your DocumentController
use App\Http\Controllers\DoctorController; 
use App\Http\Controllers\MedicalRecordController; 








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



// Authentication Routes (Login/Logout)
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->middleware('auth')
            ->name('logout');

// --- Custom Email Verification Routes ---
// These routes are for authenticated users whose email is NOT yet verified.
// They should NOT be restricted by roles like 'admin' or 'medecin'.
Route::middleware('auth' )->group(function () {
    Route::get('/email/verify-code', [App\Http\Controllers\Auth\VerificationCodeController::class, 'showVerificationForm'])
        ->name('verification.notice.code');

    Route::post('/email/verify-code', [App\Http\Controllers\Auth\VerificationCodeController::class, 'verifyCode'])
        ->name('verification.verify.code');

    Route::post('/email/resend-code', [App\Http\Controllers\Auth\VerificationCodeController::class, 'resendCode'])
        ->name('verification.resend.code');
});



Route::middleware(['auth','verified'])->group(function () {

    // Medecin (User Role) Routes - now correctly nested after verification
        Route::prefix('medecin')->middleware('medecin')->group(function () { // Add 'medecin' middleware here
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
            Route::get('/doctor/medical-records-history', [DoctorController::class, 'medicalRecordsHistory'])->name('doctor.medicalRecordsHistory');


        // Profile Routes
       Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    
    // Password change routes
    Route::get('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::put('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    

            // Regime Alimentaire Routes
        Route::resource('regimes', RegimeAlimentaireController::class);

        // Consultation Routes
        Route::resource('consultations', ConsultationController::class);
        Route::put('consultations/{consultation}/update_status', [ConsultationController::class, 'updateStatus'])
            ->name('consultations.update_status');


            // Notification Routes
Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');


        // Patient Routes
        Route::resource('patients', PatientController::class);
        Route::get('patients/{patient}/BloodCalandar', [PatientController::class, 'getBloodSugarsForCalendarTable'])
            ->name('patients.blood-calendar');
        Route::get('patients/{patient}/medical-history', [PatientController::class, 'medicalHistory'])
            ->name('patients.medical-history');
        Route::get('patients/{patient}/generate-report', [PatientController::class, 'generateReport'])
            ->name('patients.generate-report');
        Route::get('patients/search', [PatientController::class, 'search'])
            ->name('patients.search');

        // Document and Medical Record Download Routes
               Route::get('/documents/download/{document}', [DownloadController::class, 'downloadDocument'])
        ->name('documents.download');
    
    Route::get('/medical-records/download/{record}', [DownloadController::class, 'downloadMedicalRecord'])
        ->name('medical-records.download');



         Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('/create/{patient?}', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('/', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
        Route::post('/{document}/toggle-sign', [DocumentController::class, 'toggleSign'])->name('documents.toggle-sign');
        
        // Additional document routes can be added here
    });


           Route::resource('medical-records', MedicalRecordController::class)->only([
        'create', 'store', 'destroy'
    ]);
    // Specific route for downloading a medical record
    Route::get('medical-records/{medical_record}/download', [MedicalRecordController::class, 'download'])->name('medical-records.download');

    // Route to show all medical records for a specific patient
    // This route is used by the "See all Medical Records" link on the patient's profile
    Route::get('/patients/{patient}/medical-records', [MedicalRecordController::class, 'showPatientRecords'])->name('patients.medicalRecords.all');

    // Route to show a single patient's profile (used for "Back to Patient Profile" link)

     Route::get('/doctor/medical-history', [DoctorController::class, 'medicalHistory'])->name('doctor.medicalHistory');

    // Route to show detailed information for a specific consultation
    // The {consultation} wildcard will automatically inject the Consultation model
    Route::get('/doctor/consultations/{consultation}', [DoctorController::class, 'showConsultation'])->name('doctor.showConsultation');
});
   

    // Admin routes - now correctly nested after verification
    Route::prefix('admin')->middleware('admin')->group(function () { // Add 'admin' middleware here
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/doctors', [AdminController::class, 'AllDoctors'])->name('doctors.index');
        Route::get('/patients', [AdminController::class, 'AllPatients'])->name('patients');
        Route::get('/doctors/create', [AdminController::class, 'createDoctor'])->name('doctors.create');
        Route::post('/doctors', [AdminController::class, 'storeDoctor'])->name('doctors.store');


         Route::get('/profile', [ProfileController::class, 'edit'])->name( 'admin.profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('admin.profile.show');

    });

    // Profile Routes (general authenticated & verified users)

   


    // Other general authenticated & verified routes can go here
});


// --- Other Routes (if they don't require email verification, but still auth) ---
require __DIR__.'/auth.php'; // Keep this if you have other auth routes defined there

Route::get('/webtest', function () {
    return 'Web route is working!';
});

Route::get('/MyPatients', function () {
    return view('medecin.patient.show');
});