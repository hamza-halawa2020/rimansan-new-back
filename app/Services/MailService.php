<?php

namespace App\Services;

use App\Exceptions\MailRateLimitedException;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailService
{
    /**
     * @throws MailRateLimitedException
     * @throws Throwable
     */
    public function queue(string $email, Mailable $mailable): void
    {
        try {
            Mail::to($email)->queue($mailable);
        } catch (Throwable $exception) {
            Log::error('Mail queue failed.', [
                'email' => $email,
                'message' => $exception->getMessage(),
            ]);

            if ($this->isRateLimitException($exception)) {
                throw new MailRateLimitedException();
            }

            throw $exception;
        }
    }

    private function isRateLimitException(Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, '451')
            || str_contains($message, '4.7.1')
            || str_contains($message, 'ratelimit')
            || str_contains($message, 'rate limit');
    }
}
