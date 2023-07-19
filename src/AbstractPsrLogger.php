<?php

namespace yii1tech\psr\log;

use CLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Yii;

/**
 * PsrLogger is a wrapper around Yii standard {@see \CLogger}, which provides PSR compatible interface.
 *
 * This class can be used in case you work with 3rd party library, which requires PSR Log component to be passed into.
 *
 * AbstractArrayLogger is an intermediate class for {@see PsrLogger} creation.
 * Its existence required since {@see \Psr\Log\LoggerInterface} changes signature over different PHP versions.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         \Psr\Log\LoggerInterface::class => [
 *             'class' => \yii1tech\psr\log\PsrLogger::class,
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * > Note: in order to handle log context properly this class should be used in junction with {@see \yii1tech\psr\log\Logger}
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
abstract class AbstractPsrLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var \CLogger|null Yii logger to write logs into.
     */
    private $_yiiLogger;

    /**
     * @return \CLogger Yii logger instance.
     */
    public function getYiiLogger(): CLogger
    {
        if ($this->_yiiLogger === null) {
            $this->_yiiLogger = Yii::getLogger();
        }

        return $this->_yiiLogger;
    }

    /**
     * @param \CLogger|null $yiiLogger Yii logger instance to be used.
     * @return static self reference.
     */
    public function setYiiLogger(?CLogger $yiiLogger): self
    {
        $this->_yiiLogger = $yiiLogger;

        return $this;
    }

    /**
     * Logs with an arbitrary level.
     * This method should be invoked during {@see log()} method implementation.
     *
     * @param mixed $level log level.
     * @param string|\Stringable $message log message.
     * @param array $context log context.
     * @return void
     */
    protected function writeLog($level, $message, array $context = []): void
    {
        $yiiLogger = $this->getYiiLogger();

        if (!$yiiLogger instanceof Logger) {
            $context = $context['category'] ?? 'application';
        }

        $yiiLogger->log(
            $message,
            LogLevelConverter::toYii($level),
            $context
        );
    }
}