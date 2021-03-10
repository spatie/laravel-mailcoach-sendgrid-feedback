<?php

namespace Spatie\MailcoachSendgridFeedback\SendgridEvents;

use Spatie\Mailcoach\Domain\Shared\Models\Send;

class OtherEvent extends SendgridEvent
{
    public function canHandlePayload(): bool
    {
        return true;
    }

    public function handle(Send $send)
    {
    }
}
