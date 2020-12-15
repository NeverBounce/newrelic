<?php

namespace Intouch\Newrelic\Test\Handler;

use Intouch\Newrelic\Handler\NullHandler;
use PHPUnit\Framework\TestCase;
use Intouch\Newrelic\Handler\Handler;

class NullHandlerTest extends TestCase
{
    public function testImplementsInterface()
    {
        $handler = new NullHandler();

        $this->assertInstanceOf(Handler::class, $handler);
    }

    public function testHandleReturnsFalse()
    {
        $functionName = 'strpos';
        $arguments = array(
            'foobarbaz',
            'bar',
            0
        );

        $handler = new NullHandler();

        $this->assertFalse($handler->handle($functionName, $arguments));
    }
}
