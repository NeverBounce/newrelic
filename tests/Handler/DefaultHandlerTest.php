<?php

namespace Intouch\Newrelic\Test\Handler;

use Intouch\Newrelic\Handler\DefaultHandler;
use PHPUnit\Framework\TestCase;
use Intouch\Newrelic\Handler\Handler;

class DefaultHandlerTest extends TestCase
{
    public function testImplementsInterface()
    {
        $handler = new DefaultHandler();

        $this->assertInstanceOf(Handler::class, $handler);
    }
    public function testHandleCallsFunctionWithArguments()
    {
        $functionName = 'strpos';
        $arguments = array(
            'foobarbaz',
            'bar',
            0
        );

        $handler = new DefaultHandler();

        $expected = call_user_func_array($functionName, $arguments);

        $this->assertSame($expected, $handler->handle($functionName, $arguments));
    }
}
