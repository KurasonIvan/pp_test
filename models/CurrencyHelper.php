<?php

namespace app\models;

use Yii;
use yii\base\Model;

class CurrencyHelper extends Model
{
    private const COMMISSION_RETE = 0.02;
    private array $data = [];
    private string $currencyFrom;
    private string $currencyTo;
    private float $currencyValue;

    private function isFiat(): bool
    {
        foreach ($this->data as $item) {
            if ($item['symbol'] === $this->currencyTo && $item['type'] === 'fiat') {
                return true;
            }
        }

        return false;
    }

    private function isCurrencyExists(string $currency): bool
    {
        $currencyData = $this->getRatesWithCommission();
        return key_exists($currency, $currencyData);
    }

    private function getPureRates(): array
    {
        foreach ($this->data as $item) {
            $data[$item['symbol']] = $item['rateUsd'];
        }

        asort($data);
        return $data;
    }

    private function getFullCurrencyData(): array
    {
        $currencyClient = Yii::$app->currencyClient->createRequest()
            ->setMethod('GET')
            ->setUrl('/v2/rates')
            ->send();

        if ($currencyClient->isOk) {
            return $currencyClient->getData()['data'];
        } else {
            return [];
        }
    }

    private function getCrossRateWithCommission(): float
    {
        $currencyData = $this->getPureRates();
        return (1 - self::COMMISSION_RETE) * $currencyData[$this->currencyFrom] / $currencyData[$this->currencyTo];
    }

    public function init()
    {
        $this->data = $this->getFullCurrencyData();
    }

    public function setCurrencyFrom(string $currency)
    {
        $normalisedCurrency = strtoupper(trim($currency));
        $this->currencyFrom = $this->isCurrencyExists($normalisedCurrency) ? $normalisedCurrency : '';
    }

    public function setCurrencyTo(string $currency)
    {
        $normalisedCurrency = strtoupper(trim($currency));
        $this->currencyTo = $this->isCurrencyExists($normalisedCurrency) ? $normalisedCurrency : '';
    }

    public function setCurrencyValue(float $value)
    {
        $this->currencyValue = ($value >= 0.1) ? $value : 0;
    }

    public function getDataForNeedleCurrency(string $currency): array
    {
        $needleCurrencies = explode(',', $currency);
        $currencyWith = $this->getRatesWithCommission();
        $dataForResponse = [];

        foreach ($needleCurrencies as $needleCurrency) {
            $needleCurrency = strtoupper(trim($needleCurrency));
            if ($this->isCurrencyExists($needleCurrency)) {
                $dataForResponse[$needleCurrency] = $currencyWith[$needleCurrency];
            }
        }

        return $dataForResponse;
    }

    public function getRatesWithCommission(): array
    {
        foreach ($this->data as $item) {
            $data[$item['symbol']] = number_format(($item['rateUsd'] * (1 + self::COMMISSION_RETE)), 10);
        }

        asort($data);
        return $data;
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
        $rate = $this->getCrossRateWithCommission();
        $result = number_format(($rate * $this->currencyValue), ($this->isFiat()) ? 2 : 10);

        return [
            'currency_from' => $this->currencyFrom,
            'currency_to' => $this->currencyTo,
            'value' => $this->currencyValue,
            'converted_value' => $result,
            'rate' => number_format($rate,($this->isFiat()) ? 2 : 10)
        ];
    }

    public function isLoaded(): bool
    {
        return !$this->data;
    }
}