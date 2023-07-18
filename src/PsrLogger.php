<?php

namespace yii1tech\psr\log;

if (version_compare(phpversion(), '8.0', '>=')) {
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
} else {
    /**
     * {@inheritdoc}
     */
    class PsrLogger extends AbstractPsrLogger
    {
        /**
         * {@inheritdoc}
         */
        public function log($level, $message, array $context = []): void
        {
            $this->writeLog($level, $message, $context);
        }
    }
}