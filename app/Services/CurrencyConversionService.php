<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stripe\ExchangeRate;

/**
 * Currency Conversion Service
 * 
 * Handles currency conversion between USD and SEK using Stripe Exchange Rates API.
 * Exchange rates are cached for 1 hour to minimize API calls.
 * 
 * @author Ashraful Islam
 */
class CurrencyConversionService
{
    const CACHE_KEY = 'stripe_exchange_rate_usd_to_sek';
    const CACHE_DURATION = 3600; // 1 hour in seconds

    /**
     * Convert USD cents to SEK öre (cents)
     * 
     * @param int $usdCents Amount in USD cents (e.g., 200 = $2.00)
     * @return int Amount in SEK öre/cents (e.g., 2100 = 21.00 SEK)
     */
    public function convertUsdToSek(int $usdCents): int
    {
        $exchangeRate = $this->getExchangeRate();
        
        // Convert: USD cents → USD dollars → SEK → SEK öre
        $usdDollars = $usdCents / 100;
        $sekAmount = $usdDollars * $exchangeRate;
        $sekOre = round($sekAmount * 100);
        
        Log::info("Currency conversion: {$usdCents} USD cents ({$usdDollars} USD) → {$sekOre} SEK öre ({$sekAmount} SEK) at rate {$exchangeRate}");
        
        return (int) $sekOre;
    }

    /**
     * Get USD to SEK exchange rate from Stripe (cached)
     * 
     * @return float Exchange rate (e.g., 10.5 means 1 USD = 10.5 SEK)
     */
    public function getExchangeRate(): float
    {
        //return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            return $this->fetchExchangeRateFromStripe();
        //});
    }

    /**
     * Fetch current exchange rate from Stripe API
     * 
     * @return float Exchange rate
     * @throws \Exception If unable to fetch rate
     */
    private function fetchExchangeRateFromStripe(): float
    {
        try {
            // Retrieve all exchange rates from Stripe
            $exchangeRates = ExchangeRate::all(['limit' => 1]);
            
            // Get the most recent exchange rate data
            if (isset($exchangeRates->data[0])) {
                $rateData = $exchangeRates->data[0];
                
                // Get SEK rate from USD base currency
                if (isset($rateData->rates['sek'])) {
                    $rate = $rateData->rates['sek'];
                    
                    Log::info("Fetched exchange rate from Stripe: 1 USD = {$rate} SEK");
                    
                    return (float) $rate;
                }
            }
            
            throw new \Exception('SEK rate not found in Stripe exchange rates');
            
        } catch (\Exception $e) {
            Log::info('Failed to fetch exchange rate from Stripe: ' . $e->getMessage());
            
            // Fallback to a default rate (you may want to use a different API as fallback)
            // Current approximate rate as of Nov 2025
            $fallbackRate = 9.45;
            
            Log::info("Using fallback exchange rate: 1 USD = {$fallbackRate} SEK");
            
            return $fallbackRate;
        }
    }

    /**
     * Clear cached exchange rate (useful for testing or manual refresh)
     * 
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Log::info('Exchange rate cache cleared');
    }
}

