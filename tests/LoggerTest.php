<?php

namespace yii1tech\psr\log\test;

use CLogger;
use Psr\Log\LogLevel;
use Yii;
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

    /**
     * @depends testWritePsrLog
     */
    public function testYiiLogFacade(): void
    {
        $psrLogger = new ArrayLogger();

        $logger = (new Logger())
            ->setPsrLogger($psrLogger);

        Yii::setLogger($logger);

        Yii::log('test message', LogLevel::INFO, ['category' => 'context-category']);

        $logs = $psrLogger->flush();
        $this->assertFalse(empty($logs[0]));
        $this->assertSame(LogLevel::INFO, $logs[0]['level']);
        $this->assertSame('test message', $logs[0]['message']);
        $this->assertSame(['category' => 'context-category'], $logs[0]['context']);
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

    /**
     * @depends testWriteYiiLog
     */
    public function testDisableYiiLog(): void
    {
        $logger = (new Logger())
            ->enableYiiLog(false);

        $logger->log('test message', CLogger::LEVEL_INFO, 'test-category');

        $this->assertEmpty($logger->getLogs());
    }

    /**
     * @depends testWriteYiiLog
     */
    public function testWriteYiiLogContext(): void
    {
        $logger = (new Logger())
            ->enableYiiLog(true);

        $logger->log('test message', CLogger::LEVEL_INFO, ['foo' => 'bar']);

        $logs = $logger->getLogs();
        $logger->flush();

        $this->assertFalse(empty($logs[0]));
        $this->assertStringContainsString('"foo"', $logs[0][0]);
        $this->assertStringContainsString('"bar"', $logs[0][0]);

        try {
            throw new \RuntimeException('test-exception-message');
        } catch (\Throwable $exception) {
            // exception prepared
        }

        $logger->log('test message', CLogger::LEVEL_INFO, ['exception' => $exception]);

        $logs = $logger->getLogs();
        $logger->flush();

        $this->assertFalse(empty($logs[0]));
        $this->assertStringContainsString(\RuntimeException::class, $logs[0][0]);
        $this->assertStringContainsString('test-exception-message', $logs[0][0]);
    }

    /**
     * @depends testWritePsrLog
     */
    public function testGlobalLogContext(): void
    {
        $psrLogger = new ArrayLogger();

        $logger = (new Logger())
            ->setPsrLogger($psrLogger)
            ->withGlobalContext(function () {
                return [
                    'global' => 'global-context',
                ];
            });

        $logger->log('test message', CLogger::LEVEL_INFO, 'test-category');

        $logs = $psrLogger->flush();
        $this->assertFalse(empty($logs[0]));
        $this->assertArrayHasKey('global', $logs[0]['context']);
        $this->assertSame('global-context', $logs[0]['context']['global']);
    }
}