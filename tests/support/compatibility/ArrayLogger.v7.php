<?php

namespace yii1tech\psr\log\test\support;

use yii1tech\psr\log\test\support\AbstractArrayLogger;

/**
 * {@inheritdoc}
 */
class ArrayLogger extends AbstractArrayLogger
{
    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $this->writeLog($level, $message, $context);
    }
}