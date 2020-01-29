<?php

namespace Spatie\MailcoachSendgridFeedback\SendgridEvents;

use Spatie\Mailcoach\Models\Send;

class OpenEvent extends SendgridEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'open';
    }

    public function handle(Send $send)
    {
        return $send->registerOpen();
    }
}
