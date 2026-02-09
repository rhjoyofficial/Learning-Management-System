<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Public\CourseController;
use App\Http\Controllers\Api\Public\CategoryController;
use App\Http\Controllers\Api\Public\CouponController;
use App\Http\Controllers\Api\Public\CertificateVerificationController;
use App\Http\Controllers\Api\Student\StudentDashboardController;
use App\Http\Controllers\Api\Student\StudentCourseController;
use App\Http\Controllers\Api\Student\EnrollmentController;
use App\Http\Controllers\Api\Student\ProgressController;
use App\Http\Controllers\Api\Student\CertificateController;
use App\Http\Controllers\Api\Student\CheckoutController;
use App\Http\Controllers\Api\Student\SSLCommerzCheckoutController;
use App\Http\Controllers\Api\Student\BkashCheckoutController;
use App\Http\Controllers\Api\Instructor\InstructorCourseController;
use App\Http\Controllers\Api\Webhook\PaymentWebhookController;
use App\Http\Controllers\Api\Webhook\SSLCommerzIPNController;
use App\Http\Controllers\Api\Webhook\BkashCallbackController;

// ─── Auth ────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// ─── Public ──────────────────────────────────────────────────
Route::prefix('public')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{slug}', [CourseController::class, 'show']);
    Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);
});

Route::get('/verify/certificates/{certificate_number}', [CertificateVerificationController::class, 'verify']);

// ─── Student (protected) ────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
    // Dashboard
    Route::get('/dashboard', [StudentDashboardController::class, 'index']);

    // Course player
    Route::get('/courses/{course}', [StudentCourseController::class, 'show']);
    Route::get('/courses/{course}/resume', [StudentCourseController::class, 'resume']);
    Route::get('/lessons/{lesson}/watch', [StudentCourseController::class, 'watch']);

    // Enrollments
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store']);
    Route::get('/enrollments', [EnrollmentController::class, 'index']);

    // Progress
    Route::post('/lessons/{lesson}/progress', [ProgressController::class, 'update']);
    Route::get('/courses/{course}/progress', [ProgressController::class, 'show']);

    // Certificates
    Route::post('/courses/{course}/certificate', [CertificateController::class, 'generate']);
    Route::get('/courses/{course}/certificate', [CertificateController::class, 'show']);

    // Payments & Checkout
    Route::post('/courses/{course}/checkout', [CheckoutController::class, 'checkout']);
    Route::post('/courses/{course}/sslcommerz/checkout', [SSLCommerzCheckoutController::class, 'checkout']);
    Route::post('/courses/{course}/bkash/checkout', [BkashCheckoutController::class, 'checkout']);
    Route::get('/payments', fn(Request $r) => $r->user()->payments()->latest()->get());
});

// ─── Instructor (protected) ─────────────────────────────────
Route::middleware(['auth:sanctum', 'role:instructor'])->prefix('instructor')->group(function () {
    Route::get('/dashboard', fn() => response()->json(['m' => 'Instructor Area']));
    Route::get('/courses', [InstructorCourseController::class, 'index']);
    Route::get('/courses/{course}', [InstructorCourseController::class, 'show']);
    Route::post('/courses', [InstructorCourseController::class, 'store']);
    Route::put('/courses/{course}', [InstructorCourseController::class, 'update']);
});

// ─── Admin (protected) ──────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', fn() => response()->json(['m' => 'Admin Area']));
});

// ─── Webhooks (no auth — called by payment providers) ───────
Route::post('/payments/webhook', [PaymentWebhookController::class, 'handle']);
Route::post('/payments/sslcommerz/ipn', [SSLCommerzIPNController::class, 'handle']);
Route::post('/payments/bkash/callback', [BkashCallbackController::class, 'handle']);
