<?php

namespace yii1tech\psr\log;

use CLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;
use Yii;

/**
 * Logger is an enhanced version of Yii standard {@see \CLogger}, which allows passing messages to the wrapped PSR logger.
 *
 * This class can be used in case you with to utilize 3rd party PSR logger library like "Monolog" in your Yii application.
 *
 * Configuration example:
 *
 * ```php
 * require __DIR__ . '../vendor/autoload.php';
 * // ...
 *
 * Yii::setLogger(
 *     \yii1tech\psr\log\Logger::new()
 *         ->setPsrLogger(function () {
 *             $log = new \Monolog\Logger('yii');
 *             $log->pushHandler(new \Monolog\Handler\StreamHandler('path/to/your.log', \Monolog\Level::Warning));
 *
 *             return $log;
 *         })
 *         ->enableYiiLog(true)
 * );
 *
 * Yii::createWebApplication($config)->run();
 * ```
 *
 * @property \Psr\Log\LoggerInterface|\Closure|string|array|null $psrLogger related PSR logger.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Logger extends CLogger
{
    use HasGlobalContext;

    /**
     * @var bool whether original Yii logging mechanism should be used or not.
     */
    public $yiiLogEnabled = true;

    /**
     * @var int max nested level for the log context to be written into Yii log message.
     */
    public $logContextMaxNestedLevel = 3;

    /**
     * @var \Psr\Log\LoggerInterface|null related PSR logger.
     */
    private $_psrLogger;

    /**
     * @return \Psr\Log\LoggerInterface|null related PSR logger instance.
     */
    public function getPsrLogger(): ?LoggerInterface
    {
        if ($this->_psrLogger === null) {
            return null;
        }

        if (!is_object($this->_psrLogger)) {
            $this->_psrLogger = Yii::createComponent($this->_psrLogger);
        } elseif ($this->_psrLogger instanceof \Closure) {
            $this->_psrLogger = call_user_func($this->_psrLogger);
        }

        return $this->_psrLogger;
    }

    /**
     * Sets the PSR logger to pass logs to.
     * If `null` provided - no PSR logger will be used.
     *
     * @param \Psr\Log\LoggerInterface|\Closure|array|string|null $psrLogger related PSR logger.
     * @return static self reference.
     */
    public function setPsrLogger($psrLogger): self
    {
        $this->_psrLogger = $psrLogger;

        return $this;
    }

    /**
     * @see $yiiLogEnabled
     *
     * @param bool $enable whether original Yii logging mechanism should be used or not.
     * @return static self reference.
     */
    public function enableYiiLog(bool $enable = true): self
    {
        $this->yiiLogEnabled = $enable;

        return $this;
    }

    /**
     * @see $logContextMaxNestedLevel
     *
     * @param int $logContextMaxNestedLevel max nested level for the log context to be written into Yii log message.
     * @return static self reference.
     */
    public function setLogContextMaxNestedLevel(int $logContextMaxNestedLevel): self
    {
        $this->logContextMaxNestedLevel = $logContextMaxNestedLevel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function log($message, $level = 'info', $category = 'application'): void
    {
        if (is_array($category)) {
            $rawContext = array_merge(
                $this->resolveGlobalContext(),
                $category
            );
            $context = $rawContext;

            if (isset($context['category'])) {
                $category = $context['category'];
            } else {
                $category = 'application';
                $context['category'] = $category;
            }
        } else {
            $rawContext = $this->resolveGlobalContext();
            $context = array_merge(
                $rawContext,
                [
                    'category' => $category,
                ]
            );
        }

        if (($psrLogger = $this->getPsrLogger()) !== null) {
            $psrLogger->log(
                LogLevelConverter::toPsr($level),
                $message,
                $context
            );
        }

        if ($this->yiiLogEnabled) {
            parent::log(
                $message . $this->createMessageSuffixFromContext($rawContext),
                LogLevelConverter::toYii($level),
                $category
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function logGlobalContextResolutionError(Throwable $exception): void
    {
        $errorMessage = 'Unable to resolve global log context: ' . $exception->getMessage();

        if (($psrLogger = $this->getPsrLogger()) !== null) {
            $psrLogger->log(
                LogLevel::ERROR,
                $errorMessage,
                [
                    'exception' => $exception,
                ]
            );
        }

        if ($this->yiiLogEnabled) {
            parent::log($errorMessage, CLogger::LEVEL_ERROR, 'system.log');
        }
    }

    /**
     * Creates a trailing suffix for the log message from the log context.
     *
     * @param array $logContext log context.
     * @return string log message suffix.
     */
    protected function createMessageSuffixFromContext(array $logContext): string
    {
        if (empty($logContext)) {
            return '';
        }

        $logContext = $this->formatLogContext($logContext);

        return "\n\n" . $this->serializeLogContext($logContext);
    }

    /**
     * Serializes log context into a string.
     *
     * @param array $logContext raw log context.
     * @return string serialized log context.
     */
    protected function serializeLogContext(array $logContext): string
    {
        if (YII_DEBUG) {
            return json_encode($logContext, JSON_PRETTY_PRINT);
        }

        return json_encode($logContext);
    }

    /**
     * Formats log context to be suitable for string serialization.
     *
     * @param array $logContext raw log context.
     * @param int $nestedLevel current nested level.
     * @return array formatted log context.
     */
    protected function formatLogContext(array $logContext, int $nestedLevel = 0): array
    {
        if ($nestedLevel > $this->logContextMaxNestedLevel) {
            return [];
        }

        foreach ($logContext as $key => $value) {
            if (is_object($value)) {
                if ($value instanceof Throwable) {
                    $logContext[$key] = [
                        'class' => get_class($value),
                        'code' => $value->getCode(),
                        'message' => $value->getMessage(),
                        'file' => $value->getFile(),
                        'line' => $value->getLine(),
                    ];

                    continue;
                }

                if ($value instanceof \Traversable) {
                    $logContext[$key] = $this->formatLogContext(iterator_to_array($value), $nestedLevel + 1);

                    continue;
                }

                if ($value instanceof \JsonSerializable) {
                    $value = $value->jsonSerialize();
                    if (is_array($value)) {
                        $logContext[$key] = $this->formatLogContext($value, $nestedLevel + 1);

                        continue;
                    }

                    if (is_object($value)) {
                        $logContext[$key] = get_class($value);

                        continue;
                    }

                    $logContext[$key] = $value;

                    continue;
                }

                $logContext[$key] = get_class($value);

                continue;
            }

            if (is_array($value)) {
                $logContext[$key] = $this->formatLogContext($value, $nestedLevel + 1);

                continue;
            }
        }

        return $logContext;
    }

    /**
     * Creates new self instance.
     * This method can be useful when writing chain methods calls.
     *
     * @return static new self instance.
     */
    public static function new(): self
    {
        return new static();
    }
}