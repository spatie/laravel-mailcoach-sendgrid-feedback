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
        $send->registerBounce($this->getTimestamp());
    }
}
