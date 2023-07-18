<?php

namespace yii1tech\psr\log;

use CLogRoute;
use LogicException;
use Psr\Log\LoggerInterface;
use Yii;

/**
 * PsrLogRoute passes Yii log messages to PSR logger.
 *
 * This class can be used in case you with to utilize 3rd party PSR logger library like "Monolog" in your Yii application.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'log' => [
 *             'class' => \CLogRouter::class,
 *             'routes' => [
 *                 [
 *                     'class' => \yii1tech\psr\log\PsrLogRoute::class,
 *                     'psrLogger' => function () {
 *                         $log = new \Monolog\Logger('yii');
 *                         $log->pushHandler(new \Monolog\Handler\StreamHandler('path/to/your.log', \Monolog\Level::Warning));
 *
 *                         return $log;
 *                     },
 *                 ],
 *                 // ...
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * > Note: even if you use {@see \yii1tech\psr\log\Logger} as Yii logger, this log route will be unable to handle
 *   passed log context correctly.
 *
 * @property \Psr\Log\LoggerInterface|\Closure|string|array $psrLogger related PSR logger.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class PsrLogRoute extends CLogRoute
{
    /**
     * @var \Psr\Log\LoggerInterface related PSR logger.
     */
    private $_psrLogger;

    /**
     * @return \Psr\Log\LoggerInterface related PSR logger instance.
     */
    public function getPsrLogger(): LoggerInterface
    {
        if ($this->_psrLogger === null) {
            throw new LogicException('"' . get_class($this) . '::$psrLogger" must be explicitly set.');
        }

        if (!is_object($this->_psrLogger)) {
            $this->_psrLogger = Yii::createComponent($this->_psrLogger);
        } elseif ($this->_psrLogger instanceof \Closure) {
            $this->_psrLogger = call_user_func($this->_psrLogger);
        }

        return $this->_psrLogger;
    }

    /**
     * @param \Psr\Log\LoggerInterface|\Closure|array|string $psrLogger
     * @return static self reference.
     */
    public function setPsrLogger($psrLogger): self
    {
        $this->_psrLogger = $psrLogger;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function processLogs($logs): void
    {
        $psrLogger = $this->getPsrLogger();

        foreach ($logs as $log) {
            $psrLogger->log(
                LogLevelConverter::toPsr($log[1]),
                $log[0],
                $this->createLogContext($log)
            );
        }
    }

    /**
     * Creates PSR log context from Yii log entry.
     *
     * @param array $logRow raw log row obtained from Yii logger.
     * @return array PSR compatible log context.
     */
    protected function createLogContext(array $logRow): array
    {
        return [
            'category' => $logRow[2],
            'timestamp' => $logRow[3],
        ];
    }
}