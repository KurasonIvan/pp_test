<?php

namespace app\models;

use Yii;
use yii\base\Model;

class CurrencyHelper extends Model
{
    public const COMMISSION_RETE = 0.02;
    private array $data = [];
    private string $currencyFrom;
    private string $currencyTo;
    private float $currencyValue;


    public function init()
    {
        $this->data = $this->getCurrencyData();
    }

    public function setCurrencyFrom(string $currency)
    {
        $this->currencyFrom = $this->isCurrencyExists($currency) ? $currency : '';
    }

    public function setCurrencyTo(string $currency)
    {
        $this->currencyTo = $this->isCurrencyExists($currency) ? $currency : '';
    }

    public function setCurrencyValue(float $value)
    {
        $this->currencyValue = ($value >= 0.1) ? $value : 0;
    }

    private function getCurrencyData(): array
    {
        $currencyClient = Yii::$app->currencyClient->createRequest()
            ->setMethod('GET')
            ->setUrl('/v2/rates')
            ->send();

        if ($currencyClient->isOk) {
            $currencyData = $currencyClient->getData()['data'];
            $data = [];

            foreach ($currencyData as $item) {
                $data[$item['symbol']] = $item['rateUsd'];
            }
            asort($data);

            return $data;
        } else {
            return [];
        }
    }

    public function getDataForNeedleCurrency(string $currency): array
    {
        $needleCurrencies = explode(',', $currency);
        $dataForResponse = [];

        foreach ($needleCurrencies as $needleCurrency) {
            $needleCurrency = strtoupper(trim($needleCurrency));
            if ($this->isCurrencyExists($needleCurrency)) {
                $dataForResponse[$needleCurrency] = $this->data[$needleCurrency];
            }
        }

        return $dataForResponse;
    }

    public function getData(): array
    {
        return $this->data;
    }
    public function setParams(array $params): bool
    {
        $this->setCurrencyFrom($params['currency_from']);
        $this->setCurrencyTo($params['currency_to']);
        $this->setCurrencyValue($params['value']);

        return ($this->currencyTo && $this->currencyFrom && $this->currencyValue);
    }

    public function getResponseForConverter(): array
    {
        $rate = $this->getCrossRate();
        $result = round(($rate * $this->currencyValue), 2);

        return [
            'currency_from' => $this->currencyFrom,
            'currency_to' => $this->currencyTo,
            'value' => $this->currencyValue,
            'converted_value' => $result,
            'rate' => round($rate, 2)
        ];
    }

    private function getCrossRate(): float
    {
        return (1 - self::COMMISSION_RETE) * $this->data[$this->currencyFrom] / $this->data[$this->currencyTo];
    }

    public function isCurrencyExists(string $currency): bool
    {
        return key_exists($currency, $this->data);
    }
}