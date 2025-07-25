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
        
        return $this->formatCurrency($formattedAmount, $agreement->currency, $agreement->locale ?? 'en');
    }

    /**
     * Format weight based on locale from agreement
     */
    public function formatWeight(float $weight, Agreement $agreement): string
    {
        $nanograms = $this->unitConverter->toNanograms($weight);
        $formattedWeight = $nanograms / 1_000_000_000;
        $locale = $agreement->locale ?? 'en';
        
        return $this->formatNumber($formattedWeight, $locale) . ' ' . $this->getWeightUnit($locale);
    }

    /**
     * Format distance based on locale from agreement
     */
    public function formatDistance(float $distance, Agreement $agreement): string
    {
        $millimeters = $this->unitConverter->toMillimeters($distance);
        $formattedDistance = $millimeters / 1000;
        $locale = $agreement->locale ?? 'en';
        
        return $this->formatNumber($formattedDistance, $locale) . ' ' . $this->getDistanceUnit($locale);
    }

    /**
     * Format currency based on locale
     */
    private function formatCurrency(float $amount, string $currency, string $locale): string
    {
        // Use consistent fallback formatting for predictable results
        $formattedNumber = number_format($amount, 2, $this->getDecimalSeparator($locale), $this->getThousandsSeparator($locale));
        
        // Place currency symbol based on locale conventions
        if (in_array($locale, ['de', 'fr', 'es', 'it', 'nl', 'pt', 'pl', 'ru'])) {
            return $formattedNumber . ' ' . $this->getCurrencySymbol($currency);
        }
        
        return $this->getCurrencySymbol($currency) . $formattedNumber;
    }

    /**
     * Format number based on locale
     */
    private function formatNumber(float $number, string $locale): string
    {
        // Use consistent fallback formatting for predictable results
        return number_format($number, 2, $this->getDecimalSeparator($locale), $this->getThousandsSeparator($locale));
    }

    /**
     * Get locale string from locale code
     */
    private function getLocale(string $locale): string
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
        
        return $locales[$locale] ?? 'en_US';
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
     * Get decimal separator for locale
     */
    private function getDecimalSeparator(string $locale): string
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
        
        return $separators[$locale] ?? '.';
    }

    /**
     * Get thousands separator for locale
     */
    private function getThousandsSeparator(string $locale): string
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
        
        return $separators[$locale] ?? ',';
    }

    /**
     * Get weight unit for locale
     */
    private function getWeightUnit(string $locale): string
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
        
        return $units[$locale] ?? 'g';
    }

    /**
     * Get distance unit for locale
     */
    private function getDistanceUnit(string $locale): string
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
        
        return $units[$locale] ?? 'm';
    }

    /**
     * Format date based on locale from agreement
     */
    public function formatDate($date, Agreement $agreement): string
    {
        return $this->formatDateWithLocale($date, $agreement->locale ?? 'en');
    }

    /**
     * Format date with specific locale
     */
    public function formatDateWithLocale($date, ?string $locale): string
    {
        // Convert string to DateTime if needed
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        
        // Use fallback locale if null
        $locale = $locale ?? 'en';
        
        $format = $this->getDateFormat($locale);
        return $date->format($format);
    }

    /**
     * Get date format for locale
     */
    private function getDateFormat(string $locale): string
    {
        $formats = [
            'en' => 'm/d/Y',
            'de' => 'd.m.Y',
            'fr' => 'd/m/Y',
            'nl' => 'd-m-Y',
            'es' => 'd/m/Y',
            'it' => 'd/m/Y',
            'pt' => 'd/m/Y',
            'pl' => 'd.m.Y',
            'ru' => 'd.m.Y',
            'zh' => 'Y/m/d',
            'ja' => 'Y/m/d',
        ];
        
        return $formats[$locale] ?? 'm/d/Y';
    }
}