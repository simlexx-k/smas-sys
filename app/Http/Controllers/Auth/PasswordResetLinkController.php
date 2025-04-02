<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info('Password reset requested', [
            'email' => $request->email
        ]);

        $request->validate([
            'email' => 'required|email',
        ]);

        // Increase execution time limit for this operation
        $originalTimeLimit = ini_get('max_execution_time');
        set_time_limit(60); // Set to 60 seconds

        try {
            // Try alternative mail configuration if the primary one fails
            try {
                $status = Password::sendResetLink(
                    $request->only('email')
                );
                
                Log::info('Password reset link status', [
                    'email' => $request->email,
                    'status' => $status,
                    'status_text' => __($status)
                ]);
            } catch (\Exception $mailException) {
                Log::warning('Primary mail configuration failed, trying fallback', [
                    'error' => $mailException->getMessage()
                ]);
                
                // Use a fallback like log driver
                config(['mail.default' => 'log']);
                
                $status = Password::sendResetLink(
                    $request->only('email')
                );
            }

            if ($status === Password::RESET_LINK_SENT) {
                Log::info('Password reset link sent successfully', [
                    'email' => $request->email
                ]);
            } else {
                Log::warning('Password reset link not sent', [
                    'email' => $request->email,
                    'status' => $status
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending password reset link', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            // Restore original time limit
            set_time_limit($originalTimeLimit);
        }

        return back()->with('status', __('A reset link will be sent if the account exists.'));
    }
}
