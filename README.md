# yii2-sentry

This module adds [sentry.io](https://sentry.io) exception monitoring and performance tracing to Yii2.

Specifically this module uses [notamedia/yii2-sentry](https://github.com/notamedia/yii2-sentry) to add a `LogTarget` that catches exceptions and sends them to Sentry.

It also adds a custom `Logger` that hooks into the `Logger::LEVEL_PROFILE_BEGIN` and `Logger::LEVEL_PROFILE_END` log messages to build up Sentry performance Events.

That means you can just use the Yii2 default `Yii::beginProfile()` and `Yii::endProfile()` calls to track performance.

## Installation

`composer require niolab/yii2-sentry`

## Usage

Add the module to your config:

```php
...
'sentry' => [
    'class'=>\niolab\sentry\Module::class,
    'dsn' => 'https://xxxxx@yyyy.ingest.sentry.io/00000', // your sentry.io URL here
    'targetOptions' => [
        // extra options for the SentryTarget      
    ]
],
...
```

Add the module to the application bootstrap.
```php
...
'bootstrap' => ['log','sentry']
...
```