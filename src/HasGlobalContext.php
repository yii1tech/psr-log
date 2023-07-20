<?php

namespace yii1tech\psr\log;

use InvalidArgumentException;
use Throwable;

/**
 * HasGlobalContext provides global log context setup and resolution ability.
 *
 * @mixin \CComponent
 *
 * @property-write \Closure|array|null $globalContext global log context.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0.1
 */
trait HasGlobalContext
{
    /**
     * @var \Closure|array|null log context, which should be applied to each message.
     */
    private $_globalContext;

    /**
     * Sets the log context, which should be applied to each log message.
     * You can use a `\Closure` to specify calculated expression for it.
     * For example:
     *
     * ```php
     * $logger = (new Logger)
     *     ->withGlobalContext(function () {
     *         return [
     *             'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
     *         ];
     *     });
     * ```
     *
     * @param \Closure|array|null $globalLogContext global log context.
     * @return static self reference.
     */
    public function withGlobalContext($globalLogContext): self
    {
        if ($globalLogContext !== null && !is_array($globalLogContext) && !$globalLogContext instanceof \Closure) {
            throw new InvalidArgumentException('"' . get_class($this) . '::$globalLogContext" should be either an array or a `\\Closure`');
        }

        $this->_globalContext = $globalLogContext;

        return $this;
    }

    /**
     * Alias of {@see withGlobalContext()}.
     * Supports Yii magic property access.
     *
     * @param \Closure|array|null $globalLogContext global log context.
     * @return static self reference.
     */
    public function setGlobalContext($globalLogContext): self
    {
        return $this->withGlobalContext($globalLogContext);
    }

    /**
     * Returns global log context for particular message.
     * If global context is set as callable, it will be executed each time.
     *
     * @return array log context.
     */
    protected function resolveGlobalContext(): array
    {
        if ($this->_globalContext === null) {
            return [];
        }

        if ($this->_globalContext instanceof \Closure) {
            try {
                return call_user_func($this->_globalContext);
            } catch (Throwable $exception) {
                $this->logGlobalContextResolutionError($exception);

                return [];
            }
        }

        return $this->_globalContext;
    }

    /**
     * Logs the error which occurs at during global context resolution.
     *
     * @param \Throwable $exception error exception.
     * @return void
     */
    protected function logGlobalContextResolutionError(Throwable $exception): void
    {
        syslog(LOG_ERR, 'Unable to resolve global log context: ' . $exception->getMessage());
    }
}