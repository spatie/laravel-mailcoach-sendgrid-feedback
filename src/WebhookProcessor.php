<?php

namespace Spatie\MailcoachSendgridFeedback;

use Spatie\WebhookClient\Models\WebhookCall;

use Spatie\WebhookClient\WebhookProcessor as BaseWebhookProcessor;

class WebhookProcessor extends BaseWebhookProcessor
{
    protected function storeWebhook(): WebhookCall
    {
        $this->request->query->remove('secret');

        return parent::storeWebhook();
    }
}
