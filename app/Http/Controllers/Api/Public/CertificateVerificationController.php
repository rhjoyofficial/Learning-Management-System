<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;

class CertificateVerificationController extends Controller
{
    public function verify(string $certificateNumber)
    {
        $certificate = Certificate::where('certificate_number', $certificateNumber)
            ->with(['user:id,name', 'course:id,title'])
            ->first();

        if (! $certificate) {
            return response()->json([
                'valid' => false,
                'message' => 'Certificate not found.'
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'learner_name' => $certificate->user->name,
            'course_title' => $certificate->course->title,
            'issued_at' => $certificate->issued_at->toDateString(),
            'certificate_number' => $certificate->certificate_number,
        ]);
    }
}
