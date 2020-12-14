<?php declare(strict_types=1);

namespace Intouch\Newrelic\Handler;

interface Handler
{
    /**
     * @param string $functionName
     * @param array $arguments
     *
     * @return mixed
     */
    public function handle(string $functionName, array $arguments = []);
}
