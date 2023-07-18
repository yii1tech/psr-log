<?php

namespace yii1tech\psr\log;

/**
 * {@inheritdoc}
 */
class PsrLogger extends AbstractPsrLogger
{
    /**
     * {@inheritdoc}
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->writeLog($level, $message, $context);
    }
}