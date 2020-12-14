<?php declare(strict_types=1);

namespace Intouch\Newrelic\Handler;

class NullHandler implements Handler
{
    public function handle(string $functionName, array $arguments = [])
    {
        return false;
    }
}
