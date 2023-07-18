<?php

if (version_compare(phpversion(), '8.0', '>=')) {
    require __DIR__ . '/compatibility/ArrayLogger.v8.php';
} else {
    require __DIR__ . '/compatibility/ArrayLogger.v7.php';
}