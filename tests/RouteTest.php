<?php

namespace Spatie\MailcoachSendgridFeedback\Tests;

use Illuminate\Support\Facades\Route;

class RouteTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Route::sendgridFeedback('sendgrid-feedback');

        config()->set('mailcoach.sendgrid_feedback.signing_secret', 'secret');
    }

    /** @test */
    public function it_provides_a_route_macro_to_handle_webhooks()
    {
        $this->withoutExceptionHandling();

        $payload = $this->getStub('bouncePayload');


        $this
            ->post('sendgrid-feedback?secret=secret', $payload)
            ->assertSuccessful();
    }

    /** @test */
    public function it_will_not_accept_calls_with_an_invalid_signature()
    {
        $payload = $this->getStub('bouncePayload');

        $this
            ->post('sendgrid-feedback?secret=incorrect_secret')
            ->assertStatus(500);
    }
}
