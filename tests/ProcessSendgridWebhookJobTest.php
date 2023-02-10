<?php

namespace Spatie\MailcoachSendgridFeedback\Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Mailcoach\Domain\Campaign\Enums\SendFeedbackType;
use Spatie\Mailcoach\Domain\Campaign\Events\WebhookCallProcessedEvent;
use Spatie\Mailcoach\Domain\Campaign\Models\CampaignLink;
use Spatie\Mailcoach\Domain\Campaign\Models\CampaignOpen;
use Spatie\Mailcoach\Domain\Shared\Models\Send;
use Spatie\Mailcoach\Domain\Shared\Models\SendFeedbackItem;
use Spatie\MailcoachSendgridFeedback\ProcessSendgridWebhookJob;
use Spatie\WebhookClient\Models\WebhookCall;

class ProcessSendgridWebhookJobTest extends TestCase
{
    use RefreshDatabase;

    private WebhookCall $webhookCall;

    private $send;

    public function setUp(): void
    {
        parent::setUp();

        $this->webhookCall = WebhookCall::create([
            'name' => 'sendgrid',
            'payload' => $this->getStub('multipleEventsPayload'),
        ]);

        $this->send = Send::factory()->create();
        $this->send->update(['uuid' => 'test-uuid']);
        $this->send->subscriber->update(['email' => 'example@test.com']);
    }

    /** @test */
    public function it_can_handle_multiple_events()
    {
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(2, SendFeedbackItem::count());
        $this->assertEquals(SendFeedbackType::Bounce, SendFeedbackItem::first()->type);
        $this->assertTrue($this->send->is(SendFeedbackItem::first()->send));
    }

    /** @test */
    public function it_processes_a_sendgrid_complaint_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('complaintPayload')]);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, SendFeedbackItem::count());
        tap(SendFeedbackItem::first(), function (SendFeedbackItem $sendFeedbackItem) {
            $this->assertEquals(SendFeedbackType::Complaint, $sendFeedbackItem->type);
            $this->assertEquals(Carbon::createFromTimestamp(1574854444), $sendFeedbackItem->created_at);
            $this->assertTrue($this->send->is($sendFeedbackItem->send));
        });

        $this->send->subscriber->update(['email' => 'not-example@test.com']);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();
        $this->assertEquals(1, SendFeedbackItem::count());
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

        $this->send->subscriber->update(['email' => 'not-example@test.com']);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();
        $this->assertEquals(1, CampaignLink::count());
        $this->assertCount(1, CampaignLink::first()->clicks);
    }

    /** @test */
    public function it_processes_a_sendgrid_click_webhook_call_with_message_id()
    {
        $this->send->update(['transport_message_id' => '14c5d75ce93']);

        $payload = $this->getStub('clickPayload');
        unset($payload['send_uuid']);

        $this->webhookCall->update(['payload' => $payload]);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, CampaignLink::count());
        $this->assertEquals('https://example.com', CampaignLink::first()->url);
        $this->assertCount(1, CampaignLink::first()->clicks);
        $this->assertEquals(Carbon::createFromTimestamp(1574854444), CampaignLink::first()->clicks->first()->created_at);

        $this->send->subscriber->update(['email' => 'not-example@test.com']);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();
        $this->assertEquals(1, CampaignLink::count());
        $this->assertCount(1, CampaignLink::first()->clicks);
    }

    /** @test */
    public function it_can_process_a_sendgrid_open_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('openPayload')]);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertCount(1, $this->send->campaign->opens);
        $this->assertEquals(Carbon::createFromTimestamp(1574854444), $this->send->campaign->opens->first()->created_at);

        $this->send->subscriber->update(['email' => 'not-example@test.com']);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();
        $this->assertCount(1, $this->send->campaign->fresh()->opens);
    }

    /** @test */
    public function it_can_process_a_sendgrid_bounce_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('bouncePayload')]);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, SendFeedbackItem::count());
        $this->assertEquals(SendFeedbackType::Bounce, SendFeedbackItem::first()->type);
        $this->assertTrue($this->send->is(SendFeedbackItem::first()->send));
    }

    /** @test */
    public function it_wont_process_a_sendgrid_temporary_bounce_webhook_call()
    {
        $payload = $this->getStub('bouncePayload');
        $payload[0]['type'] = 'blocked';

        $this->webhookCall->update(['payload' => $payload]);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(0, SendFeedbackItem::count());
    }

    /** @test */
    public function it_will_fire_an_event_when_processing_is_complete()
    {
        Event::fake(WebhookCallProcessedEvent::class);

        $this->webhookCall->update(['payload' => $this->getStub('openPayload')]);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        Event::assertDispatched(WebhookCallProcessedEvent::class);
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

    /** @test */
    public function it_will_not_handle_events_without_send_uuid()
    {
        $this->webhookCall->update(['payload' => $this->getStub('noSendUuidPayload')]);
        (new ProcessSendgridWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(0, CampaignLink::count());
        $this->assertEquals(0, CampaignOpen::count());
        $this->assertEquals(0, SendFeedbackItem::count());
    }

    /** @test */
    public function it_wont_handle_the_same_event_ids_twice()
    {
        $call2 = WebhookCall::create([
            'name' => 'sendgrid',
            'payload' => $this->getStub('multipleEventsPayload'),
        ]);

        $job = new ProcessSendgridWebhookJob($this->webhookCall);
        $job->handle();

        $job = new ProcessSendgridWebhookJob($call2);
        $job->handle();

        $this->assertEquals(2, SendFeedbackItem::count());

        $this->assertEquals([], $call2->fresh()->payload);
    }
}
