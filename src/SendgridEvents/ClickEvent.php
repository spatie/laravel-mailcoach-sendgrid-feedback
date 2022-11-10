<?php

namespace Spatie\MailcoachSendgridFeedback\SendgridEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Shared\Models\Send;

class ClickEvent extends SendgridEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'click';
    }

    public function handle(Send $send)
    {
        $url = Arr::get($this->payload, 'url');

        if (! $url) {
            return;
        }

        $email = Arr::get($this->payload, 'email');

        if ($send->subscriber && $email !== $send->subscriber->email) {
            return;
        }

        if ($send->transactionalMail && $email !== $send->transactionalMail->to[0]['email']) {
            return;
        }

        $send->registerClick($url, $this->getTimestamp());
    }
}
