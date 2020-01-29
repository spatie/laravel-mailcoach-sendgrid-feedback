<?php

namespace Spatie\MailcoachSendgridFeedback;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MailcoachSendgridFeedbackServiceProvider extends ServiceProvider
{
    public function register()
    {
        Route::macro('sendgridFeedback', fn (string $url) => Route::post($url, '\\' . SendgridWebhookController::class));

        Event::listen(MessageSending::class, AddUniqueArgumentsMailHeader::class);
    }
}
