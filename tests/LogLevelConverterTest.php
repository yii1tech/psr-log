<?php

namespace yii1tech\psr\log\test;

use CLogger;
use Psr\Log\LogLevel;
use yii1tech\psr\log\LogLevelConverter;

class LogLevelConverterTest extends TestCase
{
    /**
     * Data provide for {@see testToPsr()}
     *
     * @return array[]
     */
    public static function dataProviderToPsr(): array
    {
        return [
            [CLogger::LEVEL_ERROR, LogLevel::ERROR],
            [CLogger::LEVEL_WARNING, LogLevel::WARNING],
            [CLogger::LEVEL_INFO, LogLevel::INFO],
            [CLogger::LEVEL_TRACE, LogLevel::DEBUG],
            [CLogger::LEVEL_PROFILE, LogLevel::DEBUG],

            [LogLevel::EMERGENCY, LogLevel::EMERGENCY],
        ];
    }

    /**
     * @dataProvider dataProviderToPsr
     *
     * @param mixed $level
     * @param mixed $expectedResult
     * @return void
     */
    public function testToPsr($level, $expectedResult): void
    {
        $this->assertSame($expectedResult, LogLevelConverter::toPsr($level));
    }

    /**
     * Data provide for {@see testToYii()}
     *
     * @return array[]
     */
    public static function dataProviderToYii(): array
    {
        return [
            [LogLevel::ALERT, CLogger::LEVEL_ERROR],
            [LogLevel::EMERGENCY, CLogger::LEVEL_ERROR],
            [LogLevel::CRITICAL, CLogger::LEVEL_ERROR],
            [LogLevel::ERROR, CLogger::LEVEL_ERROR],
            [LogLevel::WARNING, CLogger::LEVEL_WARNING],
            [LogLevel::NOTICE, CLogger::LEVEL_WARNING],
            [LogLevel::INFO, CLogger::LEVEL_INFO],
            [LogLevel::DEBUG, CLogger::LEVEL_TRACE],

            [CLogger::LEVEL_PROFILE, CLogger::LEVEL_PROFILE],
        ];
    }

    /**
     * @dataProvider dataProviderToYii
     *
     * @param mixed $level
     * @param mixed $expectedResult
     * @return void
     */
    public function testToYii($level, $expectedResult): void
    {
        $this->assertSame($expectedResult, LogLevelConverter::toYii($level));
    }
}