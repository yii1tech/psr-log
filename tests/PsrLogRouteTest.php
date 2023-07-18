<?php

namespace yii1tech\psr\log\test;

use CLogger;
use Psr\Log\LogLevel;
use yii1tech\psr\log\PsrLogRoute;
use yii1tech\psr\log\test\support\ArrayLogger;

class PsrLogRouteTest extends TestCase
{
    public function testSetupPsrLogger(): void
    {
        $logRoute = new PsrLogRoute();

        $psrLogger = new ArrayLogger();

        $logRoute->setPsrLogger($psrLogger);

        $this->assertSame($psrLogger, $logRoute->getPsrLogger());

        $logRoute->setPsrLogger([
            'class' => ArrayLogger::class,
        ]);

        $this->assertTrue($logRoute->getPsrLogger() instanceof ArrayLogger);
    }

    public function testWriteLog(): void
    {
        $yiiLogger = new CLogger();
        $psrLogger = new ArrayLogger();

        $logRoute = (new PsrLogRoute())
            ->setPsrLogger($psrLogger);

        $yiiLogger->log('test message', CLogger::LEVEL_INFO, 'test-category');

        $logRoute->collectLogs($yiiLogger, true);

        $logs = $psrLogger->flush();
        $this->assertFalse(empty($logs[0]));
        $this->assertSame(LogLevel::INFO, $logs[0]['level']);
        $this->assertSame('test message', $logs[0]['message']);
        $this->assertSame('test-category', $logs[0]['context']['category']);
        $this->assertFalse(empty($logs[0]['context']['timestamp']));
    }
}