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

Yii::log('psr message', LogLevel::INFO, 'psr-category'); // same as `Yii::log('psr message', CLogger::LEVEL_INFO, 'psr-category');` 

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

You may also specify a global log context, which should be written with every message. For example:

```php
<?php

// set custom logger:
Yii::setLogger(
    \yii1tech\psr\log\Logger::new()
        ->setPsrLogger(function () {
            // ...
        })
        ->withGlobalContext(function () {
            $context = [];
            
            // log remote IP address if available:
            if (!empty($_SERVER['REMOTE_ADDR'])) {
                $context['ip'] = $_SERVER['REMOTE_ADDR'];
            }
            
            // log authenticated user ID, if available:
            $webUser = Yii::app()->getComponent('user', false);
            if ($webUser !== null && !$webUser->getIsGuest()) {
                $context['auth_user_id'] = $webUser->getId();
            }
            
            return $context;
        })
);
```


## PSR Log Route <span id="psr-log-route"></span>

It is not necessary to `\yii1tech\psr\log\Logger` if you need to pass logs to PSR logger.
As an alternative you can add `\yii1tech\psr\log\PsrLogRoute` log route to the standard Yii "log" component.

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

> Note: even if you use `\yii1tech\psr\log\Logger` as Yii logger, this `\yii1tech\psr\log\PsrLogRoute` will be unable to handle
  passed log context correctly.


## Wrap Yii logger into PSR logger <span id="wrap-yii-logger-into-psr-logger"></span>

There is another use case related to PSR logger besides bootstrapping eternal log storage.
Sometimes 3rd party libraries may require PSR logger instance to be passed to them in order to function.
For example, imagine we have a 3rd party library for "daemon" application running:

```php
<?php

namespace vendor\daemon;

use Psr\Log\LoggerInterface;

class DaemonApplication
{
    public function __construct(LoggerInterface $logger)
    {
        // ....
    }
}
```

You can use `\yii1tech\psr\log\PsrLogger` to wrap standard Yii logging mechanism into PSR interface.

Application configuration example:

```php
<?php

return [
    'components' => [
        \Psr\Log\LoggerInterface::class => [
            'class' => \yii1tech\psr\log\PsrLogger::class,
        ],
        // ...
    ],
    // ...
];
```

Now while working with our example external "daemon" application, we can use following code:

```php
<?php

use Psr\Log\LoggerInterface;
use vendor\daemon\DaemonApplication;

$daemon = new DaemonApplication(Yii::app()->getComponent(LoggerInterface::class));
// ...
```

> Note: in order to handle log context properly `\yii1tech\psr\log\PsrLogger` should be used in junction with `\yii1tech\psr\log\Logger`.

