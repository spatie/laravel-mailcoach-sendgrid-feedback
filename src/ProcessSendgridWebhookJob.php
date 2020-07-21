<?php

namespace Spatie\MailcoachSendgridFeedback;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Events\WebhookCallProcessedEvent;
use Spatie\Mailcoach\Models\Send;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\ProcessWebhookJob;
use Spatie\Mailcoach\Support\Config;

class ProcessSendgridWebhookJob extends ProcessWebhookJob
{
    public function __construct(WebhookCall $webhookCall)
    {
        parent::__construct($webhookCall);

        $this->queue = config('mailcoach.perform_on_queue.process_feedback_job');

        $this->connection = $this->connection ?? Config::getQueueConnection();
    }

    public function handle()
    {
        $payload = $this->webhookCall->payload;

        foreach ($payload as $rawEvent) {
            $this->handleRawEvent($rawEvent);
        }

        event(new WebhookCallProcessedEvent($this->webhookCall));
    }

    protected function handleRawEvent(array $rawEvent)
    {
        if (!$send = $this->getSend($rawEvent)) {
            return;
        }

        $sendgridEvent = SendgridEventFactory::createForPayload($rawEvent);

        $sendgridEvent->handle($send);
    }

    protected function getSend(array $rawEvent): ?Send
    {
        $sendUuid = Arr::get($rawEvent, 'send_uuid');
        
        if (! $sendUuid) {
            return null;
        }

        return Send::findByUuid($sendUuid);
    }
}
