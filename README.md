<p align="center">
    <a href="https://github.com/yii1tech" target="_blank">
        <img src="https://avatars.githubusercontent.com/u/134691944" height="100px">
    </a>
    <h1 align="center">Yii1 PSR Log Extension</h1>
    <br>
</p>

This extension allows integration with PSR compatible logger for Yii1.
Its usage in particular it allows usage of [Monolog](https://github.com/Seldaek/monolog) logger.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://img.shields.io/packagist/v/yii1tech/psr-log.svg)](https://packagist.org/packages/yii1tech/psr-log)
[![Total Downloads](https://img.shields.io/packagist/dt/yii1tech/psr-log.svg)](https://packagist.org/packages/yii1tech/psr-log)
[![Build Status](https://github.com/yii1tech/psr-log/workflows/build/badge.svg)](https://github.com/yii1tech/psr-log/actions)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii1tech/psr-log
```

or add

```json
"yii1tech/psr-log": "*"
```

to the "require" section of your composer.json.


Usage
-----

This extension allows integration with PSR compatible logger for Yii1.
It provides several instruments for that. Please choose the one suitable for your particular needs. 


## Wrap PSR logger into Yii logger <span id="wrap-psr-logger-into-yii-logger"></span>

The most common use case for PSR logger involvement into Yii application is usage of 3rd party log library like [Monolog](https://github.com/Seldaek/monolog).
This can be achieved using `\yii1tech\psr\log\Logger` instance as Yii logger. Its instance should be passed to `\Yii::setLogger()`
before the application instantiation.

Application entry script example:

```php
<?php
// file '/public/index.php'

require __DIR__ . '../vendor/autoload.php';
// ...

// set custom logger:
Yii::setLogger(
    \yii1tech\psr\log\Logger::new()
        ->setPsrLogger(function () {
            // use Monolog as internal PSR logger:
            $log = new \Monolog\Logger('yii');
            $log->pushHandler(new \Monolog\Handler\StreamHandler('path/to/your.log', \Monolog\Level::Warning));

            return $log;
        })
        ->enableYiiLog(true) // whether to continue passing logs to standard Yii log mechanism or not
);

// create and run Yii application:
Yii::createWebApplication($config)->run();
```

`\yii1tech\psr\log\Logger` passes all messages logged via `Yii::log()` to the related PSR logger, which stores them according to its
own internal logic.

Also, usage of `\yii1tech\psr\log\Logger` grands you several additional benefits:

- It allows usage of `\Psr\log\LogLevel` constants for log level specification instead of `\CLogger` ones.
- It allows passing log context as array as a 3rd argument of the `Yii::log()` method, and saving it into Yii logs.

For example:

```php
<?php

use Psr\log\LogLevel;

Yii::log('psr message', LogLevel::INFO); // same as `Yii::log('psr message', CLogger::LEVEL_INFO);` 

Yii::log('context message', LogLevel::INFO, [
    'foo' => 'bar', // specifying log context, which will be passed to the related PSR logged, and added as JSON to the Yii log message, if it is enabled 
]);

try {
    // ...
} catch (\Throwable $exception) {
    Yii::log('context exception', LogLevel::ERROR, [
        'exception' => $exception, // exception data such as class, message, file, line and so on will be logged
    ]);
}
```


## PSR Log Route <span id="psr-log-route"></span>

Application configuration example:

```php
<?php

return [
    'components' => [
        'log' => [
            'class' => \CLogRouter::class,
            'routes' => [
                [
                    'class' => \yii1tech\psr\log\PsrLogRoute::class,
                    'psrLogger' => function () {
                        $log = new \Monolog\Logger('yii');
                        $log->pushHandler(new \Monolog\Handler\StreamHandler('path/to/your.log', \Monolog\Level::Warning));
 
                        return $log;
                    },
                ],
                // ...
            ],
        ],
        // ...
    ],
    // ...
];
```


## Wrap Yii logger into PSR logger <span id="wrap-yii-logger-into-psr-logger"></span>
