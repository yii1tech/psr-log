<?php

namespace yii1tech\psr\log;

use CLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Throwable;
use Yii;

/**
 * PsrLogger is a wrapper around Yii standard {@see \CLogger}, which provides PSR compatible interface.
 *
 * This class can be used in case you work with 3rd party library, which requires PSR Log component to be passed into.
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
class PsrLogger implements LoggerInterface
{
    use LoggerTrait;
    use HasGlobalContext;

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
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $context = array_merge(
            $this->resolveGlobalContext(),
            $context
        );

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

    /**
     * {@inheritdoc}
     */
    protected function logGlobalContextResolutionError(Throwable $exception): void
    {
        $errorMessage = 'Unable to resolve global log context: ' . $exception->getMessage();

        $this->getYiiLogger()->log($errorMessage, CLogger::LEVEL_ERROR, 'system.log');
    }

    /**
     * Creates new self instance.
     * This method can be useful when writing chain methods calls.
     *
     * @since 1.0.1
     *
     * @param mixed ...$args constructor arguments.
     * @return static new self instance.
     */
    public static function new(...$args): self
    {
        return new static(...$args);
    }
}