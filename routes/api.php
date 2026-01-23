<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Public\CourseController;
use App\Http\Controllers\Api\Public\CategoryController;
use App\Http\Controllers\Api\Student\CheckoutController;
use App\Http\Controllers\Api\Student\ProgressController;
use App\Http\Controllers\Api\Student\EnrollmentController;
use App\Http\Controllers\Api\Student\CertificateController;
use App\Http\Controllers\Api\Webhook\PaymentWebhookController;
use App\Http\Controllers\Api\Instructor\InstructorCourseController;
use App\Http\Controllers\Api\Public\CertificateVerificationController;
use App\Http\Controllers\Api\Student\SSLCommerzCheckoutController;
use App\Http\Controllers\Api\Webhook\SSLCommerzIPNController;
use App\Http\Controllers\Api\Student\BkashCheckoutController;
use App\Http\Controllers\Api\Webhook\BkashCallbackController;
use App\Http\Controllers\Api\Public\CouponController;
use App\Http\Controllers\Api\Student\StudentDashboardController;
use App\Http\Controllers\Api\Student\StudentCourseController;

/*
|--------------------------------------------------------------------------
| Public Routes (Unauthenticated)
|--------------------------------------------------------------------------
*/

// Authentication Routes (Rate Limited)
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public Course & Category Routes
Route::prefix('public')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{slug}', [CourseController::class, 'show']);
});

// Certificate Verification (Public)
Route::get('/verify/certificates/{certificate_number}', [CertificateVerificationController::class, 'verify']);

// Coupon Validation (Authenticated or Rate Limited)
Route::post('/coupons/validate', [CouponController::class, 'validateCoupon'])
    ->middleware('throttle:20,1');

/*
|--------------------------------------------------------------------------
| Payment Webhook Routes (Rate Limited for Security)
|--------------------------------------------------------------------------
*/

Route::post('/payments/webhook', [PaymentWebhookController::class, 'handle'])
    ->middleware('throttle:100,1');
Route::post('/payments/sslcommerz/ipn', [SSLCommerzIPNController::class, 'handle'])
    ->middleware('throttle:100,1');
Route::post('/payments/bkash/callback', [BkashCallbackController::class, 'handle'])
    ->middleware('throttle:100,1');

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:student')->prefix('student')->group(function () {
        // Dashboard
        Route::get('/dashboard', [StudentDashboardController::class, 'index']);

        // Course Player
        Route::get('/courses/{course}', [StudentCourseController::class, 'show']);
        Route::get('/courses/{course}/resume', [StudentCourseController::class, 'resume']);
        Route::get('/lessons/{lesson}/watch', [StudentCourseController::class, 'watch']);

        // Enrollment
        Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store']);
        Route::get('/enrollments', [EnrollmentController::class, 'index']);

        // Progress Tracking
        Route::post('/lessons/{lesson}/progress', [ProgressController::class, 'update']);
        Route::get('/courses/{course}/progress', [ProgressController::class, 'show']);

        // Certificates
        Route::post('/courses/{course}/certificate', [CertificateController::class, 'generate']);
        Route::get('/courses/{course}/certificate', [CertificateController::class, 'show']);

        // Payment & Checkout (Different methods with specific routes)
        Route::post('/courses/{course}/checkout', [CheckoutController::class, 'checkout']);
        Route::post('/courses/{course}/checkout/bkash', [BkashCheckoutController::class, 'checkout']);
        Route::post('/courses/{course}/checkout/sslcommerz', [SSLCommerzCheckoutController::class, 'checkout']);
        Route::get('/payments', fn(Request $r) => $r->user()->payments()->latest()->get());
    });

    /*
    |--------------------------------------------------------------------------
    | Instructor Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:instructor')->prefix('instructor')->group(function () {
        Route::get('/dashboard', fn() => response()->json(['message' => 'Instructor Dashboard']));

        // Course Management
        Route::get('/courses', [InstructorCourseController::class, 'index']);
        Route::get('/courses/{course}', [InstructorCourseController::class, 'show']);
        Route::post('/courses', [InstructorCourseController::class, 'store']);
        Route::put('/courses/{course}', [InstructorCourseController::class, 'update']);
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', fn() => response()->json(['message' => 'Admin Dashboard']));
    });
});
