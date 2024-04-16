<?php

use yii\web\Response;

$params = require __DIR__ . '/params.php';

$config = [
    'id' => 'api_test',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'format' => Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'formatters' => [
                Response::FORMAT_JSON => [
                    'class' => '\yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG,
                ],
            ]
        ],
        'currencyClient' => [
            'class' => 'yii\httpclient\Client',
            'baseUrl' => 'https://api.coincap.io',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'enableSession' => false,
            'identityClass' => 'app\models\DummyIdentity',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'pattern' => 'api/v1',
                    'route' => 'api/rates',
                    'verb' => 'GET',
                ],
                [
                    'pattern' => 'api/v1',
                    'route' => 'api/convert',
                    'verb' => 'POST',
                ],
            ],
        ],
    ],
    'params' => $params,
];

return $config;
