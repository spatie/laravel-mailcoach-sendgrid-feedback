<?php

namespace Spatie\MailcoachSendgridFeedback\Tests;

use Spatie\MailcoachSendgridFeedback\SendgridSignatureValidator;
use Spatie\MailcoachSendgridFeedback\SendgridWebhookConfig;
use Spatie\WebhookClient\WebhookConfig;

class SendgridSignatureValidatorTest extends TestCase
{
    private WebhookConfig $config;

    private SendgridSignatureValidator $validator;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = SendgridWebhookConfig::get();

        $this->validator = new SendgridSignatureValidator();
    }

    /** @test */
    public function dummy_test()
    {
        $this->assertTrue(true);
    }
}
