<?php

namespace yii1tech\psr\log\test;

use CConsoleApplication;
use CLogger;
use CMap;
use Yii;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        Yii::setLogger(new CLogger());

        $this->mockApplication();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->destroyApplication();

        Yii::setLogger(null);
    }

    /**
     * Populates Yii::app() with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = CConsoleApplication::class)
    {
        Yii::setApplication(null);

        new $appClass(CMap::mergeArray([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'components' => [
                'log' => [
                    'class' => \CLogRoute::class,
                ],
            ],
        ], $config));
    }

    /**
     * Destroys Yii application by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::setApplication(null);
    }
}