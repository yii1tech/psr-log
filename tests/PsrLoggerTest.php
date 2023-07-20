<?php

namespace yii1tech\psr\log\test;

use CLogger;
use Psr\Log\LogLevel;
use Yii;
use yii1tech\psr\log\PsrLogger;

class PsrLoggerTest extends TestCase
{
    public function testSetupYiiLogger(): void
    {
        $psrLogger = new PsrLogger();

        $yiiLogger = new CLogger();

        $psrLogger->setYiiLogger($yiiLogger);

        $this->assertSame($yiiLogger, $psrLogger->getYiiLogger());
    }

    public function testGetDefaultYiiLogger(): void
    {
        $psrLogger = new PsrLogger();

        $yiiLogger = $psrLogger->getYiiLogger();

        $this->assertTrue($yiiLogger instanceof CLogger);
        $this->assertSame($yiiLogger, Yii::getLogger());
    }

    public function testWriteLog(): void
    {
        $yiiLogger = new CLogger();

        $logger = (new PsrLogger())
            ->setYiiLogger($yiiLogger);

        $logger->log(LogLevel::INFO, 'test message', ['category' => 'context-category']);

        $logs = $yiiLogger->getLogs();
        $yiiLogger->flush();

        $this->assertFalse(empty($logs[0]));
        $this->assertSame('test message', $logs[0][0]);
        $this->assertSame(LogLevel::INFO, $logs[0][1]);
        $this->assertSame('context-category', $logs[0][2]);

        $logger->log(LogLevel::INFO, 'test message', ['foo' => 'bar']);

        $logs = $yiiLogger->getLogs();
        $yiiLogger->flush();

        $this->assertFalse(empty($logs[0]));
        $this->assertSame('application', $logs[0][2]);
    }

    /**
     * @depends testWriteLog
     */
    public function testGlobalLogContext(): void
    {
        $yiiLogger = new CLogger();

        $logger = (new PsrLogger())
            ->setYiiLogger($yiiLogger);

        $logger->withGlobalContext(function () {
            return [
                'category' => 'global-category',
            ];
        });

        $logger->log('test message', CLogger::LEVEL_INFO);

        $logs = $yiiLogger->getLogs();
        $yiiLogger->flush();
        $this->assertFalse(empty($logs[0]));
        $this->assertSame('global-category', $logs[0][2]);

        $logger->log('test message', CLogger::LEVEL_INFO, [
            'category' => 'test-category',
        ]);

        $logs = $yiiLogger->getLogs();
        $yiiLogger->flush();
        $this->assertFalse(empty($logs[0]));
        $this->assertSame('test-category', $logs[0][2]);
    }
}