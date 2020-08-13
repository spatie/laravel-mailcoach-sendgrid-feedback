<?php

namespace Spatie\MailcoachSendgridFeedback\SendgridEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\Send;

class PermanentBounceEvent extends SendgridEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'bounce';
    }

    public function handle(Send $send)
    {
        if (Arr::get($this->payload, 'email') !== $send->subscriber->email) {
            return;
        }

        $send->registerBounce($this->getTimestamp());
    }
}
