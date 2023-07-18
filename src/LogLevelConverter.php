<?php

namespace yii1tech\psr\log;

use CLogger;
use Psr\log\LogLevel;

/**
 * LogLevelConverter allows converting log level specification values from Yii to PSR and vice versa.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class LogLevelConverter
{
    /**
     * Converts given log level into PSR one.
     *
     * @param string|mixed $level raw log level.
     * @return string|mixed normalized log level.
     */
    public static function toPsr($level)
    {
        switch ($level) {
            case CLogger::LEVEL_ERROR:
                return LogLevel::ERROR;
            case CLogger::LEVEL_WARNING:
                return LogLevel::WARNING;
            case CLogger::LEVEL_INFO:
                return LogLevel::INFO;
            case CLogger::LEVEL_TRACE:
            case CLogger::LEVEL_PROFILE:
                return LogLevel::DEBUG;
        }

        return $level;
    }

    /**
     * Converts given log level into Yii one.
     *
     * @param string|mixed $level raw log level.
     * @return string|mixed normalized log level.
     */
    public static function toYii($level)
    {
        switch ($level) {
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::EMERGENCY:
            case LogLevel::ERROR:
                return CLogger::LEVEL_ERROR;
            case LogLevel::NOTICE:
            case LogLevel::WARNING:
                return CLogger::LEVEL_WARNING;
            case LogLevel::INFO:
                return CLogger::LEVEL_INFO;
            case LogLevel::DEBUG:
                return CLogger::LEVEL_TRACE;
        }

        return $level;
    }
}