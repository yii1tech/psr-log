<?php

namespace yii1tech\psr\log\test\support;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class ArrayLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var array[] written log entries.
     */
    public $logs = [];

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }

    public function flush(): array
    {
        $logs = $this->logs;

        $this->logs = [];

        return $logs;
    }
}