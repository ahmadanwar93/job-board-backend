<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = User::findOrFail($id);

    // Verify the hash matches the user's email
    if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification link.');
    }

    // Check if already verified
    if ($user->hasVerifiedEmail()) {
        return redirect(env('FRONTEND_URL') . '/email-verified?already_verified=true');
    }

    // Mark as verified
    if ($user->markEmailAsVerified()) {
        event(new Verified($user));
    }

    return redirect(env('FRONTEND_URL') . '/email-verified?success=true');
})->middleware(['signed'])->name('verification.verify');
