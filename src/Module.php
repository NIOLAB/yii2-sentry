<?php
/**
 * Created by PhpStorm.
 * User: Lenovo
 * Date: 29-11-2018
 * Time: 10:21
 *
 * @OA\Info(
 *   version="v1",
 *   title="Gurbz API",
 *   description="Gurbz organization backend API",
 *   @OA\Contact(
 *     name="Harry Doddema",
 *     email="harry@niomail.nl",
 *   ),
 * )
 *
 * @OA\Server(
 *   url=API_BASE_URL,
 *   description="Gurbz API",
 * )
 * @OA\SecurityScheme(
 *     description="Supply your own access token",
 *     type="http",
 *     securityScheme="bearer_token",
 *     name="bearer_token",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\OpenApi(
 *   security={
 *         {"bearer_token": {}}
 *     }
 *)
 *
 */

namespace NIOLAB\sentry;

use app\helpers\ArrayHelper;
use NIOLAB\sentry\log\SentryPerformanceLogger;
use notamedia\sentry\SentryTarget;
use Yii;
use yii\base\Application;
use yii\base\Event;
use yii\debug\LogTarget;

class Module extends \yii\base\Module implements \yii\base\BootstrapInterface {

    public ?string $dsn;
    public array $targetOptions = [];

    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'startTransaction']);
        $app->on(Application::EVENT_AFTER_REQUEST, [$this, 'finishTransaction']);

        \Yii::$container->set('yii\log\Logger', [
            'class' => SentryPerformanceLogger::class
        ]);
        Yii::setLogger(Yii::createObject(SentryPerformanceLogger::class));
        $defaultConfig = [
            'enabled' => !YII_ENV_DEV,
            'dsn' => $this->dsn,
            'levels' => ['error', 'warning'],
            'except' => ['yii\debug*','yii\web*'],
            // Write the context information (the default is true):
            'context' => true,
            // Additional options for `Sentry\init`:
            'clientOptions' => [
                'release' => \NIOLAB\sentry\helpers\GitInfo::commit(),
                'traces_sample_rate' => 0.2,
                'environment' => YII_ENV
            ],
        ];
        $app->getLog()->targets['sentry'] = new SentryTarget(ArrayHelper::merge($defaultConfig,$this->targetOptions));
    }

    public function startTransaction(Event $event)
    {
        $transactionContext = new \Sentry\Tracing\TransactionContext();
        $transactionContext->setName(Yii::$app->request->method.' /'.Yii::$app->request->pathInfo);
        $transactionContext->setOp('http.request');
        // Start the transaction
        $transaction = \Sentry\startTransaction($transactionContext);
        // Set the current transaction as the current span so we can retrieve it later
        \Sentry\SentrySdk::getCurrentHub()->setSpan($transaction);

    }

    public function finishTransaction(Event $event)
    {
        $transaction = \Sentry\SentrySdk::getCurrentHub()->getTransaction();
        if ($transaction !== null) {
            $transaction->finish();
        }
    }
}