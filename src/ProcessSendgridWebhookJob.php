<?php

namespace Spatie\MailcoachSendgridFeedback;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Campaign\Events\WebhookCallProcessedEvent;
use Spatie\Mailcoach\Domain\Shared\Models\Send;
use Spatie\Mailcoach\Domain\Shared\Support\Config;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\ProcessWebhookJob;

class ProcessSendgridWebhookJob extends ProcessWebhookJob
{
    public function __construct(WebhookCall $webhookCall)
    {
        parent::__construct($webhookCall);

        $this->queue = config('mailcoach.campaigns.perform_on_queue.process_feedback_job');

        $this->connection = $this->connection ?? Config::getQueueConnection();
    }

    public function handle()
    {
        $payload = $this->webhookCall->payload;

        $payload = array_map(function ($rawEvent) {
            return $this->handleRawEvent($rawEvent);
        }, $payload);

        $this->webhookCall->update(['payload' => array_filter($payload)]);

        event(new WebhookCallProcessedEvent($this->webhookCall));
    }

    protected function handleRawEvent(array $rawEvent): ?array
    {
        if (! $send = $this->getSend($rawEvent)) {
            return null;
        }

        if (! $this->isFirstOfThisSendgridMessage($rawEvent)) {
            return null;
        }

        $sendgridEvent = SendgridEventFactory::createForPayload($rawEvent);

        $sendgridEvent->handle($send);

        return $rawEvent;
    }

    protected function getSend(array $rawEvent): ?Send
    {
        $sendUuid = Arr::get($rawEvent, 'send_uuid');

        if (! $sendUuid) {
            return null;
        }

        return Send::findByUuid($sendUuid);
    }

    protected function isFirstOfThisSendgridMessage(array $rawEvent): bool
    {
        $firstMessageId = (int) WebhookCall::query()
            ->where('payload', 'LIKE', "%\"sg_event_id\":\"{$rawEvent['sg_event_id']}\"%")
            ->orWhere('payload', 'LIKE', "%\"sg_event_id\": \"{$rawEvent['sg_event_id']}\"%")
            ->min('id');

        return $this->webhookCall->id === $firstMessageId;
    }
}
