<?php

namespace Spatie\MailcoachSendgridFeedback\Tests;

use Carbon\Carbon;
use Spatie\Mailcoach\Enums\SendFeedbackType;
use Spatie\Mailcoach\Models\CampaignLink;
use Spatie\Mailcoach\Models\CampaignOpen;
use Spatie\Mailcoach\Models\Send;
use Spatie\Mailcoach\Models\SendFeedbackItem;
use Spatie\MailcoachSendgridFeedback\ProcessSendgridWebhookJob;
use Spatie\WebhookClient\Models\WebhookCall;

class ProcessSendgridWebhookJobTest extends TestCase
{
    private WebhookCall $webhookCall;

    private $send;

    public function setUp(): void
    {
        parent::setUp();

        $this->webhookCall = WebhookCall::create([
            'name' => 'sendgrid',
            'payload' => $this->getStub('multipleEventsPayload'),
        ]);

        $this->send = factory(Send::class)->create();
        $this->send->update(['uuid' => 'test-uuid']);

        $this->send->campaign->update([
            'track_opens' => true,
            'track_clicks' => true,
        ]);
    }

    /** @test */
    public function it_can_handle_multiple_events()
    {
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(2, SendFeedbackItem::count());
        $this->assertEquals(SendFeedbackType::BOUNCE, SendFeedbackItem::first()->type);
        $this->assertTrue($this->send->is(SendFeedbackItem::first()->send));
        $this->assertDeleted($this->webhookCall);
    }

    /** @test */
    public function it_processes_a_sendgrid_complaint_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('complaintPayload')]);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, SendFeedbackItem::count());
        tap(SendFeedbackItem::first(), function (SendFeedbackItem $sendFeedbackItem) {
            $this->assertEquals(SendFeedbackType::COMPLAINT, $sendFeedbackItem->type);
            $this->assertEquals(Carbon::createFromTimestamp(1574854444), $sendFeedbackItem->created_at);
            $this->assertTrue($this->send->is($sendFeedbackItem->send));
        });
    }

    /** @test */
    public function it_processes_a_sendgrid_click_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('clickPayload')]);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, CampaignLink::count());
        $this->assertEquals('https://example.com', CampaignLink::first()->url);
        $this->assertCount(1, CampaignLink::first()->clicks);
        $this->assertEquals(Carbon::createFromTimestamp(1574854444), CampaignLink::first()->clicks->first()->created_at);
    }

    /** @test */
    public function it_can_process_a_sendgrid_open_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('openPayload')]);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertCount(1, $this->send->campaign->opens);
        $this->assertEquals(Carbon::createFromTimestamp(1574854444), $this->send->campaign->opens->first()->created_at);
    }

    /** @test */
    public function it_deletes_the_webhookcall_after_processing()
    {
        $this->webhookCall->update(['payload' => $this->getStub('clickPayload')]);

        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertDeleted($this->webhookCall);
    }

    /** @test */
    public function it_will_not_handle_unrelated_events()
    {
        $this->webhookCall->update(['payload' => $this->getStub('otherPayload')]);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(0, CampaignLink::count());
        $this->assertEquals(0, CampaignOpen::count());
        $this->assertEquals(0, SendFeedbackItem::count());
    }

    /** @test */
    public function it_does_nothing_when_it_cannot_find_the_transport_message_id()
    {
        $data = $this->webhookCall->payload;
        $data[0]['send_uuid'] = 'some-other-uuid';
        $data[1]['send_uuid'] = 'some-other-uuid';

        $this->webhookCall->update([
            'payload' => $data,
        ]);

        $job = new ProcessSendgridWebhookJob($this->webhookCall);

        $job->handle();

        $this->assertEquals(0, SendFeedbackItem::count());
    }
}
