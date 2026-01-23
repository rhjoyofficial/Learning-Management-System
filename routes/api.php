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
// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // RBAC Routes
    Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
        // Dashboard
        Route::get('/dashboard', [StudentDashboardController::class, 'index']);

        // Course player
        Route::get('/courses/{course}', [StudentCourseController::class, 'show']);
        Route::get('/courses/{course}/resume', [StudentCourseController::class, 'resume']);

        // Watch lesson
        Route::get('/lessons/{lesson}/watch', [StudentCourseController::class, 'watch']);
    });

    Route::middleware('role:instructor')->prefix('instructor')->group(function () {
        Route::get('/dashboard', fn() => response()->json(['m' => 'Instructor Area']));
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', fn() => response()->json(['m' => 'Admin Area']));
    });
});

Route::prefix('public')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{slug}', [CourseController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'role:instructor'])->prefix('instructor')->group(function () {
    Route::get('/courses', [InstructorCourseController::class, 'index']);
    Route::get('/courses/{course}', [InstructorCourseController::class, 'show']);
    Route::post('/courses', [InstructorCourseController::class, 'store']);
    Route::put('/courses/{course}', [InstructorCourseController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store']);
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
    Route::post('/lessons/{lesson}/progress', [ProgressController::class, 'update']);
    Route::get('/courses/{course}/progress', [ProgressController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
    Route::post('/courses/{course}/certificate', [CertificateController::class, 'generate']);
    Route::get('/courses/{course}/certificate', [CertificateController::class, 'show']);
});

Route::get('/verify/certificates/{certificate_number}', [CertificateVerificationController::class, 'verify']);

Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
    Route::post('/courses/{course}/checkout', [CheckoutController::class, 'checkout']);
    Route::get('/payments', fn(Request $r) => $r->user()->payments()->latest()->get());
});

Route::post('/payments/webhook', [PaymentWebhookController::class, 'handle']);

Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
    Route::post('/courses/{course}/checkout', [SSLCommerzCheckoutController::class, 'checkout']);
});

Route::post('/payments/sslcommerz/ipn', [SSLCommerzIPNController::class, 'handle']);

Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
    Route::post('/courses/{course}/bkash/checkout', [BkashCheckoutController::class, 'checkout']);
});

Route::post('/payments/bkash/callback', [BkashCallbackController::class, 'handle']);

Route::post('/public/coupons/validate', [CouponController::class, 'validateCoupon']);
