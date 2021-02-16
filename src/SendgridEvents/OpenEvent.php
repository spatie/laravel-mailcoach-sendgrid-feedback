<?php

namespace Spatie\MailcoachSendgridFeedback\SendgridEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Shared\Models\Send;

class OpenEvent extends SendgridEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'open';
    }

    public function handle(Send $send)
    {
        if (Arr::get($this->payload, 'email') !== $send->subscriber->email) {
            return;
        }

        $send->registerOpen($this->getTimestamp());
    }
}
