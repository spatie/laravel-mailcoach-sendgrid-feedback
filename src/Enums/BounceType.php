<?php

namespace Spatie\MailcoachSendgridFeedback\Enums;

/** reference: https://docs.sendgrid.com/for-developers/tracking-events/event */
enum BounceType: string
{
    case Deferred = 'Deferred';
    case Bounce = 'Bounce';
    case Blocked = 'Blocked';

    public static function softBounces(): array
    {
        return [
            self::Deferred->value,
            self::Blocked->value,
        ];
    }
}
