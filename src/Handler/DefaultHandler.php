<?php declare(strict_types=1);

namespace Intouch\Newrelic\Handler;

class DefaultHandler implements Handler
{
    public function handle(string $functionName, array $arguments = [])
    {
        return call_user_func_array($functionName, $arguments);
    }
}
