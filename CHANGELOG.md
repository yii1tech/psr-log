Yii1 PSR Log extension
======================

1.0.3, July 28, 2023
--------------------

- Bug: Fixed compatibility with "psr/log" 1.x at PHP 8.x (klimov-paul)


1.0.2, July 20, 2023
--------------------

- Bug: Fixed "Class 'Psr\log\LogLevel' not found" error (klimov-paul)


1.0.1, July 20, 2023
--------------------

- Enh: Added ability for global log context setup at `PsrLogger` (klimov-paul)
- Enh: Added `PsrLogger::new()` static method for a new instance creation (klimov-paul)
- Enh: Added `Logger::$logMessageContextSeparator` allowing to control string separator for log context in Yii log messages (klimov-paul)


1.0.0, July 19, 2023
--------------------

- Initial release.
