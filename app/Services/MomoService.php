<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MomoService
{
    private string $baseUrl;
    private string $environment;
    private string $collectionPrimaryKey;
    private string $collectionUserId;
    private string $collectionApiKey;
    private string $disbursementPrimaryKey;
    private string $disbursementUserId;
    private string $disbursementApiKey;
    private ?string $callbackUrl;
    private string $currency;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.momo.base_url', 'https://sandbox.momodeveloper.mtn.com'), '/');
        $this->environment = config('services.momo.environment', 'sandbox');
        $this->collectionPrimaryKey = config('services.momo.collection.primary_key', '');
        $this->collectionUserId = config('services.momo.collection.user_id', '');
        $this->collectionApiKey = config('services.momo.collection.api_key', '');
        $this->disbursementPrimaryKey = config('services.momo.disbursement.primary_key', '');
        $this->disbursementUserId = config('services.momo.disbursement.user_id', '');
        $this->disbursementApiKey = config('services.momo.disbursement.api_key', '');
        $this->callbackUrl = config('services.momo.callback_url');
        $this->currency = config('services.momo.currency', 'EUR');
    }

    /**
     * Obtain an OAuth2 access token for the Collection API.
     * Token is cached for its lifetime minus a 60-second buffer.
     */
    public function getCollectionAccessToken(): string
    {
        $cacheKey = 'momo_collection_token';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $credentials = base64_encode($this->collectionUserId . ':' . $this->collectionApiKey);

        $response = Http::withHeaders([
            'Authorization'              => 'Basic ' . $credentials,
            'Ocp-Apim-Subscription-Key' => $this->collectionPrimaryKey,
            'Content-Length'            => '0',
        ])->post($this->baseUrl . '/collection/token/');

        if (!$response->successful()) {
            throw new \Exception('Failed to obtain MoMo collection access token: ' . $response->body());
        }

        $data       = $response->json();
        $token      = $data['access_token'];
        $expiresIn  = max(($data['expires_in'] ?? 3600) - 60, 60);

        Cache::put($cacheKey, $token, now()->addSeconds($expiresIn));

        return $token;
    }

    /**
     * Obtain an OAuth2 access token for the Disbursement API.
     */
    public function getDisbursementAccessToken(): string
    {
        $cacheKey = 'momo_disbursement_token';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $credentials = base64_encode($this->disbursementUserId . ':' . $this->disbursementApiKey);

        $response = Http::withHeaders([
            'Authorization'              => 'Basic ' . $credentials,
            'Ocp-Apim-Subscription-Key' => $this->disbursementPrimaryKey,
            'Content-Length'            => '0',
        ])->post($this->baseUrl . '/disbursement/token/');

        if (!$response->successful()) {
            throw new \Exception('Failed to obtain MoMo disbursement access token: ' . $response->body());
        }

        $data      = $response->json();
        $token     = $data['access_token'];
        $expiresIn = max(($data['expires_in'] ?? 3600) - 60, 60);

        Cache::put($cacheKey, $token, now()->addSeconds($expiresIn));

        return $token;
    }

    /**
     * Initiate a Request to Pay (Collection API).
     * Returns 202 Accepted — the actual result is delivered via callback or polling.
     *
     * @param  float       $amount
     * @param  string      $phone       MSISDN (e.g. "46733123454")
     * @param  string      $referenceId UUID to track this transaction
     * @param  string|null $currency    Defaults to config value
     * @param  string      $payerMessage
     * @param  string      $payeeNote
     * @return array{reference_id: string, status: string}
     */
    public function requestToPay(
        float $amount,
        string $phone,
        string $referenceId,
        ?string $currency = null,
        string $payerMessage = 'Payment for PiqDrop delivery',
        string $payeeNote = 'PiqDrop escrow payment'
    ): array {
        $token    = $this->getCollectionAccessToken();
        $currency = $currency ?? $this->currency;

        $headers = [
            'Authorization'              => 'Bearer ' . $token,
            'X-Reference-Id'            => $referenceId,
            'X-Target-Environment'      => $this->environment,
            'Ocp-Apim-Subscription-Key' => $this->collectionPrimaryKey,
            'Content-Type'              => 'application/json',
        ];

        if ($this->callbackUrl) {
            $headers['X-Callback-Url'] = $this->callbackUrl;
        }

        $body = [
            'amount'       => (string) $amount,
            'currency'     => $currency,
            'externalId'   => Str::uuid()->toString(),
            'payer'        => [
                'partyIdType' => 'MSISDN',
                'partyId'     => $phone,
            ],
            'payerMessage' => $payerMessage,
            'payeeNote'    => $payeeNote,
        ];

        $response = Http::withHeaders($headers)
            ->post($this->baseUrl . '/collection/v1_0/requesttopay', $body);

        if ($response->status() !== 202) {
            Log::error('MoMo requestToPay failed', [
                'reference_id' => $referenceId,
                'status'       => $response->status(),
                'body'         => $response->body(),
            ]);
            throw new \Exception('Failed to initiate MoMo payment: ' . $response->body());
        }

        return ['reference_id' => $referenceId, 'status' => 'pending'];
    }

    /**
     * Poll the Collection API for the status of a Request to Pay.
     */
    public function getPaymentStatus(string $referenceId): array
    {
        $token = $this->getCollectionAccessToken();

        $response = Http::withHeaders([
            'Authorization'              => 'Bearer ' . $token,
            'X-Target-Environment'      => $this->environment,
            'Ocp-Apim-Subscription-Key' => $this->collectionPrimaryKey,
        ])->get($this->baseUrl . '/collection/v1_0/requesttopay/' . $referenceId);

        if (!$response->successful()) {
            throw new \Exception('Failed to get MoMo payment status: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Initiate a disbursement (transfer) to a mobile wallet.
     *
     * @param  float       $amount
     * @param  string      $phone       MSISDN of the payee
     * @param  string      $referenceId UUID to track this transaction
     * @param  string|null $currency    Defaults to config value
     * @param  string      $payerMessage
     * @param  string      $payeeNote
     * @return array{reference_id: string, status: string}
     */
    public function disburse(
        float $amount,
        string $phone,
        string $referenceId,
        ?string $currency = null,
        string $payerMessage = 'PiqDrop rider payout',
        string $payeeNote = 'Earnings from PiqDrop deliveries'
    ): array {
        $token    = $this->getDisbursementAccessToken();
        $currency = $currency ?? $this->currency;

        $headers = [
            'Authorization'              => 'Bearer ' . $token,
            'X-Reference-Id'            => $referenceId,
            'X-Target-Environment'      => $this->environment,
            'Ocp-Apim-Subscription-Key' => $this->disbursementPrimaryKey,
            'Content-Type'              => 'application/json',
        ];

        if ($this->callbackUrl) {
            $headers['X-Callback-Url'] = rtrim($this->callbackUrl, '/') . '/disbursement';
        }

        $body = [
            'amount'       => (string) $amount,
            'currency'     => $currency,
            'externalId'   => Str::uuid()->toString(),
            'payee'        => [
                'partyIdType' => 'MSISDN',
                'partyId'     => $phone,
            ],
            'payerMessage' => $payerMessage,
            'payeeNote'    => $payeeNote,
        ];

        $response = Http::withHeaders($headers)
            ->post($this->baseUrl . '/disbursement/v1_0/transfer', $body);

        if ($response->status() !== 202) {
            Log::error('MoMo disbursement failed', [
                'reference_id' => $referenceId,
                'status'       => $response->status(),
                'body'         => $response->body(),
            ]);
            throw new \Exception('Failed to initiate MoMo disbursement: ' . $response->body());
        }

        return ['reference_id' => $referenceId, 'status' => 'pending'];
    }

    /**
     * Poll the Disbursement API for the status of a transfer.
     */
    public function getDisbursementStatus(string $referenceId): array
    {
        $token = $this->getDisbursementAccessToken();

        $response = Http::withHeaders([
            'Authorization'              => 'Bearer ' . $token,
            'X-Target-Environment'      => $this->environment,
            'Ocp-Apim-Subscription-Key' => $this->disbursementPrimaryKey,
        ])->get($this->baseUrl . '/disbursement/v1_0/transfer/' . $referenceId);

        if (!$response->successful()) {
            throw new \Exception('Failed to get MoMo disbursement status: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Map MTN MoMo status strings to internal application status values.
     */
    public function mapMomoStatus(string $momoStatus): string
    {
        return match (strtoupper($momoStatus)) {
            'SUCCESSFUL' => 'succeeded',
            'FAILED'     => 'failed',
            'PENDING'    => 'pending',
            default      => 'processing',
        };
    }
}
