<?php

namespace Spatie\MailcoachSendgridFeedback;

use Spatie\MailcoachSendgridFeedback\SendgridEvents\ClickEvent;
use Spatie\MailcoachSendgridFeedback\SendgridEvents\ComplaintEvent;
use Spatie\MailcoachSendgridFeedback\SendgridEvents\OpenEvent;
use Spatie\MailcoachSendgridFeedback\SendgridEvents\OtherEvent;
use Spatie\MailcoachSendgridFeedback\SendgridEvents\PermanentBounceEvent;
use Spatie\MailcoachSendgridFeedback\SendgridEvents\SendgridEvent;

class SendgridEventFactory
{
    protected static array $sendgridEvents = [
        ClickEvent::class,
        ComplaintEvent::class,
        OpenEvent::class,
        PermanentBounceEvent::class,
    ];

    public static function createForPayload(array $payload): SendgridEvent
    {
        $sendgridEvent = collect(static::$sendgridEvents)
            ->map(fn (string $sendgridEventClass) => new $sendgridEventClass($payload))
            ->first(fn (SendgridEvent $sendgridEvent) => $sendgridEvent->canHandlePayload());

        return $sendgridEvent ?? new OtherEvent($payload);
    }
}
