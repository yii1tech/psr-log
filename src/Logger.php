<?php

namespace yii1tech\psr\log;

use CLogger;
use Psr\Log\LoggerInterface;
use Yii;

/**
 * Logger is an enhanced version of Yii standard {@see \CLogger}, which allows passing messages to the wrapped PSR logger.
 *
 * This class can be used in case you with to utilize 3rd party PSR logger library like "Monolog" in your Yii application.
 *
 * @property \Psr\Log\LoggerInterface|string|array|null $psrLogger related PSR logger.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Logger extends CLogger
{
    /**
     * @var bool whether original Yii logging mechanism should be used or not.
     */
    public $yiiLogEnabled = true;

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
        }

        return $this->_psrLogger;
    }

    /**
     * @param \Psr\Log\LoggerInterface|string|array|null $psrLogger
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
     * {@inheritdoc}
     */
    public function log($message, $level = 'info', $category = 'application'): void
    {
        if (is_array($category)) {
            $context = $category;

            if (isset($context['category'])) {
                $category = $context['category'];
            } else {
                $category = 'application';
                $context['category'] = $category;
            }
        } else {
            $context = [
                'category' => $category,
            ];
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
                $message,
                LogLevelConverter::toYii($level),
                $category
            );
        }
    }
}