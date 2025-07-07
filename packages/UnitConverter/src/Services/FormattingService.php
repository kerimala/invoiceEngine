<?php

namespace InvoicingEngine\UnitConverter\Services;

use App\Models\Agreement;

class FormattingService
{
    private UnitConverterService $unitConverter;

    public function __construct(UnitConverterService $unitConverter)
    {
        $this->unitConverter = $unitConverter;
    }

    /**
     * Format pricing based on locale from agreement
     */
    public function formatPricing(float $amount, Agreement $agreement): string
    {
        $cents = $this->unitConverter->toCents($amount);
        $formattedAmount = $cents / 100;
        
        return $this->formatCurrency($formattedAmount, $agreement->currency, $agreement->language);
    }

    /**
     * Format weight based on locale from agreement
     */
    public function formatWeight(float $weight, Agreement $agreement): string
    {
        $nanograms = $this->unitConverter->toNanograms($weight);
        $formattedWeight = $nanograms / 1_000_000_000;
        
        return $this->formatNumber($formattedWeight, $agreement->language) . ' ' . $this->getWeightUnit($agreement->language);
    }

    /**
     * Format distance based on locale from agreement
     */
    public function formatDistance(float $distance, Agreement $agreement): string
    {
        $millimeters = $this->unitConverter->toMillimeters($distance);
        $formattedDistance = $millimeters / 1000;
        
        return $this->formatNumber($formattedDistance, $agreement->language) . ' ' . $this->getDistanceUnit($agreement->language);
    }

    /**
     * Format currency based on locale
     */
    private function formatCurrency(float $amount, string $currency, string $language): string
    {
        // Use consistent fallback formatting for predictable results
        $formattedNumber = number_format($amount, 2, $this->getDecimalSeparator($language), $this->getThousandsSeparator($language));
        
        // Place currency symbol based on locale conventions
        if (in_array($language, ['de', 'fr', 'es', 'it', 'nl', 'pt', 'pl', 'ru'])) {
            return $formattedNumber . ' ' . $this->getCurrencySymbol($currency);
        }
        
        return $this->getCurrencySymbol($currency) . $formattedNumber;
    }

    /**
     * Format number based on locale
     */
    private function formatNumber(float $number, string $language): string
    {
        // Use consistent fallback formatting for predictable results
        return number_format($number, 2, $this->getDecimalSeparator($language), $this->getThousandsSeparator($language));
    }

    /**
     * Get locale string from language code
     */
    private function getLocale(string $language): string
    {
        $locales = [
            'en' => 'en_US',
            'de' => 'de_DE',
            'fr' => 'fr_FR',
            'es' => 'es_ES',
            'it' => 'it_IT',
            'nl' => 'nl_NL',
            'pt' => 'pt_PT',
            'pl' => 'pl_PL',
            'ru' => 'ru_RU',
            'zh' => 'zh_CN',
            'ja' => 'ja_JP',
        ];
        
        return $locales[$language] ?? 'en_US';
    }

    /**
     * Get currency symbol
     */
    private function getCurrencySymbol(string $currency): string
    {
        $symbols = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'JPY' => '¥',
            'CHF' => 'CHF',
            'CAD' => 'C$',
            'AUD' => 'A$',
        ];
        
        return $symbols[$currency] ?? $currency;
    }

    /**
     * Get decimal separator for language
     */
    private function getDecimalSeparator(string $language): string
    {
        $separators = [
            'en' => '.',
            'de' => ',',
            'fr' => ',',
            'es' => ',',
            'it' => ',',
            'nl' => ',',
            'pt' => ',',
            'pl' => ',',
            'ru' => ',',
        ];
        
        return $separators[$language] ?? '.';
    }

    /**
     * Get thousands separator for language
     */
    private function getThousandsSeparator(string $language): string
    {
        $separators = [
            'en' => ',',
            'de' => '.',
            'fr' => ' ',
            'es' => '.',
            'it' => '.',
            'nl' => '.',
            'pt' => '.',
            'pl' => ' ',
            'ru' => ' ',
        ];
        
        return $separators[$language] ?? ',';
    }

    /**
     * Get weight unit for language
     */
    private function getWeightUnit(string $language): string
    {
        $units = [
            'en' => 'g',
            'de' => 'g',
            'fr' => 'g',
            'es' => 'g',
            'it' => 'g',
            'nl' => 'g',
            'pt' => 'g',
            'pl' => 'g',
            'ru' => 'г',
            'zh' => '克',
            'ja' => 'グラム',
        ];
        
        return $units[$language] ?? 'g';
    }

    /**
     * Get distance unit for language
     */
    private function getDistanceUnit(string $language): string
    {
        $units = [
            'en' => 'm',
            'de' => 'm',
            'fr' => 'm',
            'es' => 'm',
            'it' => 'm',
            'nl' => 'm',
            'pt' => 'm',
            'pl' => 'm',
            'ru' => 'м',
            'zh' => '米',
            'ja' => 'メートル',
        ];
        
        return $units[$language] ?? 'm';
    }
}