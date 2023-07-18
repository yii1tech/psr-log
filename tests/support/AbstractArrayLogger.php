<?php

namespace yii1tech\psr\log\test\support;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * AbstractArrayLogger is an intermediate class for {@see ArrayLogger} creation.
 *
 * Its existence required since {@see \Psr\Log\LoggerInterface} changes signature over different PHP versions.
 */
abstract class AbstractArrayLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var array[] written log entries.
     */
    public $logs = [];

    protected function baseLog($level, $message, array $context = []): void
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