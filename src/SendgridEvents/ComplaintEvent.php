<?php

namespace Spatie\MailcoachSendgridFeedback\SendgridEvents;

use Spatie\Mailcoach\Models\Send;

class ComplaintEvent extends SendgridEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'spamreport';
    }

    public function handle(Send $send)
    {
        $send->registerComplaint();
    }
}
