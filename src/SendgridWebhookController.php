<?php

namespace Spatie\MailcoachSendgridFeedback;

use Illuminate\Http\Request;

class SendgridWebhookController
{
    public function __invoke(Request $request)
    {
        $webhookConfig = SendgridWebhookConfig::get();

        (new WebhookProcessor($request, $webhookConfig))->process();

        return response()->json(['message' => 'ok']);
    }
}
