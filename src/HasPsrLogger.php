<?php

namespace yii1tech\psr\log;

use Psr\Log\LoggerInterface;
use Yii;

/**
 * HasPsrLogger adds ability to configure related PSR compatible logger.
 *
 * @mixin \CComponent
 *
 * @property \Psr\Log\LoggerInterface|string|array|null $psrLogger related PSR logger.
 */
trait HasPsrLogger
{

}