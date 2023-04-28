<?php

namespace Spatie\MailcoachSendgridFeedback\SendgridEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Shared\Models\Send;
use Spatie\MailcoachSendgridFeedback\Enums\BounceType;

class SoftBounceEvent extends SendgridEvent
{
    public function canHandlePayload(): bool
    {
        return in_array($this->event, BounceType::softBounces(), true);
    }

    public function handle(Send $send)
    {
        if (Arr::get($this->payload, 'email') !== $send->subscriber->email) {
            return;
        }

        $send->registerBounce($this->getTimestamp(), softBounce: true);
    }
}
