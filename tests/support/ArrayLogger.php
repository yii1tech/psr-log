<?php

namespace yii1tech\psr\log\test\support;

if (version_compare(phpversion(), '8.0', '>=')) {
    class ArrayLogger extends AbstractArrayLogger
    {
        public function log($level, string|\Stringable $message, array $context = []): void
        {
            $this->baseLog($level, $message, $context);
        }
    }
} else {
    class ArrayLogger extends AbstractArrayLogger
    {
        public function log($level, $message, array $context = []): void
        {
            $this->baseLog($level, $message, $context);
        }
    }
}