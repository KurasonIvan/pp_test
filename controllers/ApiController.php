<?php

namespace app\controllers;

use app\models\CurrencyHelper;
use yii\rest\Controller;
use Yii;

class ApiController extends Controller
{
    private const RATES_METHOD = 'rates';
    private const CONVERT_METHOD = 'convert';
    private const SUCCESS_STATUS = 'success';
    private const ERROR_STATUS = 'error';
    private const SUCCESS_CODE = '200';
    private const BAD_REQUEST_CODE = '404';
    private const METHOD_NOT_ALLOWED_CODE = '405';

    public function actionRates(string $method, string $currency = null)
    {
        if (strtolower($method) === self::RATES_METHOD) {

            $currencyHelper = new CurrencyHelper();
            $currencyHelper->init();
            $dataForResponse = $currencyHelper->getData();

            if (empty($dataForResponse)) {
                return $this->asJson([
                    'status' => self::ERROR_STATUS,
                    'code' => 403,
                    'message' => "Sorry, service is not available in your region."
                ]);
            }

            if (isset($currency)) {
                $dataForResponse = $currencyHelper->getDataForNeedleCurrency($currency);
                if (empty($dataForResponse)) {
                    return $this->asJson([
                        'status' => self::ERROR_STATUS,
                        'code' => self::BAD_REQUEST_CODE,
                        'message' => "Something wrong. Please, check parameter 'currency'."
                    ]);
                }
            }

            return $this->asJson([
                'status' => self::SUCCESS_STATUS,
                'code' => self::SUCCESS_CODE,
                'data' => $dataForResponse
            ]);
        } else {
            return $this->asJson([
                'status' => self::ERROR_STATUS,
                'code' => self::METHOD_NOT_ALLOWED_CODE,
                'message' => "Value of parameter 'method' is not correct for this request type. Use 'rates'."
            ]);
        }
    }

    public function actionConvert(string $method)
    {
        if (strtolower($method) === self::CONVERT_METHOD) {

            $params = Yii::$app->request->getBodyParams();

            $currencyHelper = new CurrencyHelper();
            $currencyHelper->init();
            $currencyHelper->setParams($params);

            if ($currencyHelper->setParams($params)) {
                $data = $currencyHelper->getResponseForConverter();
            } else {
                return $this->asJson([
                    'status' => self::ERROR_STATUS,
                    'code' => self::BAD_REQUEST_CODE,
                    'message' => "Something wrong. Please, check parameters."
                ]);
            }

            return $this->asJson([
                'status' => self::SUCCESS_STATUS,
                'code' => self::SUCCESS_CODE,
                'data' => $data
            ]);
        } else {
            return $this->asJson([
                'status' => self::ERROR_STATUS,
                'code' => self::METHOD_NOT_ALLOWED_CODE,
                'message' => "Value of parameter 'method' is not correct for this request type. Use 'convert'."
            ]);
        }
    }
}
