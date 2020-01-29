<?php

namespace Spatie\MailcoachSendgridFeedback\Tests;

use Spatie\MailcoachSendgridFeedback\AddUniqueArgumentsMailHeader;

class AddUniqueArgumentsMailHeaderTest extends TestCase
{
    /** @test */
    public function the_listener_does_not_contain_syntax_errors()
    {
        new AddUniqueArgumentsMailHeader();

        $this->assertTrue(true);
    }
}
