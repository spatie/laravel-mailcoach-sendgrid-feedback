<?php

namespace Spatie\MailcoachSendgridFeedback;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class MailcoachSendgridFeedbackServiceProvider extends ServiceProvider
{
    public function register()
    {
        Route::macro('sendgridFeedback', fn (string $url) => Route::post("{$url}/{mailerConfigKey?}", '\\' . SendgridWebhookController::class));

        Event::listen(MessageSending::class, AddUniqueArgumentsMailHeader::class);
        Event::listen(MessageSent::class, StoreTransportMessageId::class);
    }

    public function boot()
    {
        Mail::extend('sendgrid', function (array $config) {
            $key = $config['key'] ?? config('services.sendgrid.key');

            return (new SendgridTransportFactory())->create(
                Dsn::fromString("sendgrid+api://{$key}@default")
            );
        });
    }
}
