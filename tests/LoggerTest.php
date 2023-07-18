<?php

namespace yii1tech\psr\log\test;

use yii1tech\psr\log\Logger;

class LoggerTest extends TestCase
{
    public function testSetUpYiiLogEnabled(): void
    {
        $logger = new Logger();

        $logger->enableYiiLog(false);
        $this->assertFalse($logger->yiiLogEnabled);

        $logger->enableYiiLog(true);
        $this->assertTrue($logger->yiiLogEnabled);
    }
}