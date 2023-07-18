<?php

namespace yii1tech\psr\log\test;

use CLogger;
use Psr\Log\LogLevel;
use yii1tech\psr\log\Logger;
use yii1tech\psr\log\test\support\ArrayLogger;

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

    public function testSetupPsrLogger(): void
    {
        $logger = new Logger();

        $psrLogger = new ArrayLogger();

        $logger->setPsrLogger($psrLogger);

        $this->assertSame($psrLogger, $logger->getPsrLogger());

        $logger->setPsrLogger([
            'class' => ArrayLogger::class,
        ]);

        $this->assertTrue($logger->getPsrLogger() instanceof ArrayLogger);

        $logger->setPsrLogger(function () {
            return new ArrayLogger();
        });

        $this->assertTrue($logger->getPsrLogger() instanceof ArrayLogger);
    }

    public function testWritePsrLog(): void
    {
        $psrLogger = new ArrayLogger();

        $logger = (new Logger())
            ->setPsrLogger($psrLogger);

        $logger->log('test message', CLogger::LEVEL_INFO, 'test-category');

        $logs = $psrLogger->flush();
        $this->assertFalse(empty($logs[0]));
        $this->assertSame(LogLevel::INFO, $logs[0]['level']);
        $this->assertSame('test message', $logs[0]['message']);
        $this->assertSame(['category' => 'test-category'], $logs[0]['context']);

        $logger->log('test message', CLogger::LEVEL_INFO, ['category' => 'context-category']);

        $logs = $psrLogger->flush();
        $this->assertFalse(empty($logs[0]));
        $this->assertSame(['category' => 'context-category'], $logs[0]['context']);

        $logger->log('test message', CLogger::LEVEL_INFO, ['foo' => 'bar']);

        $logs = $psrLogger->flush();
        $this->assertFalse(empty($logs[0]));
        $this->assertSame(
            [
                'foo' => 'bar',
                'category' => 'application',
            ],
            $logs[0]['context']
        );
    }

    public function testWriteYiiLog(): void
    {
        $logger = (new Logger())
            ->enableYiiLog(true);

        $logger->log('test message', CLogger::LEVEL_INFO, 'test-category');

        $logs = $logger->getLogs();
        $logger->flush();

        $this->assertFalse(empty($logs[0]));
        $this->assertSame('test message', $logs[0][0]);
        $this->assertSame(LogLevel::INFO, $logs[0][1]);
        $this->assertSame('test-category', $logs[0][2]);

        $logger->log('test message', CLogger::LEVEL_INFO, ['category' => 'context-category']);

        $logs = $logger->getLogs();
        $logger->flush();

        $this->assertFalse(empty($logs[0]));
        $this->assertSame('context-category', $logs[0][2]);

        $logger->log('test message', CLogger::LEVEL_INFO, ['foo' => 'bar']);

        $logs = $logger->getLogs();
        $logger->flush();

        $this->assertFalse(empty($logs[0]));
        $this->assertSame('application', $logs[0][2]);

        $logger->log('test message', LogLevel::INFO, 'test-category');

        $logs = $logger->getLogs();
        $logger->flush();

        $this->assertFalse(empty($logs[0]));
        $this->assertSame(LogLevel::INFO, $logs[0][1]);
    }

    public function testDisableYiiLog(): void
    {
        $logger = (new Logger())
            ->enableYiiLog(false);

        $logger->log('test message', CLogger::LEVEL_INFO, 'test-category');

        $this->assertEmpty($logger->getLogs());
    }
}