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
        $email = Arr::get($this->payload, 'email');

        if ($send->subscriber && $email !== $send->subscriber->email) {
            return;
        }

        if ($send->transactionalMail && $email !== $send->transactionalMail->to[0]['email']) {
            return;
        }

        $send->registerOpen($this->getTimestamp());
    }
}
