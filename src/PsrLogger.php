<?php
/**
 * Splits actual class declaration into 2 separated branches, allowing support both PHP 8.x and PHP 7.x.
 * This is necessary since {@see \Psr\Log\LoggerInterface} changes signature over different PHP versions.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */

if (version_compare(phpversion(), '8.0', '>=')) {
    require __DIR__ . '/compatibility/PsrLogger.v8.php';
} else {
    require __DIR__ . '/compatibility/PsrLogger.v7.php';
}