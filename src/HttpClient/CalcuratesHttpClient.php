<?php

declare(strict_types=1);

namespace Calcurates\HttpClient;

use Calcurates\Logger;
use Calcurates\WCCalcurates;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

/**
 * Http client for Calcurates API.
 */
class CalcuratesHttpClient
{
    /**
     * Calcurates API key for auth.
     *
     * @var string
     */
    private $api_key;

    /**
     * Calcurates API URL.
     *
     * @var string
     */
    private $api_url;

    /**
     * Debug level mode.
     *
     * @var string
     */
    private $debug_mode;

    public function __construct(string $api_key, string $api_url, string $debug_mode)
    {
        $this->api_key = $api_key;
        $this->api_url = $api_url;
        $this->debug_mode = $debug_mode;
    }

    /**
     * Get rates from Calcurates server.
     */
    public function get_rates(array $rates_request_body): ?array
    {
        return $this->request($rates_request_body, '/api/woocommerce/rates');
    }

    private function request(array $request_body, string $path): ?array
    {
        $args = [
            'user-agent' => 'calcurates/module-woocommerce/'.WCCalcurates::get_version(),
            'compress' => true,
            'decompress' => true,
            'timeout' => 10,
            'method' => 'POST',
            'headers' => [
                'X-API-KEY' => null,
                'Content-Type' => 'application/json',
            ],
            'body' => \wp_json_encode($request_body),
        ];

        if ('all' === $this->debug_mode) {
            Logger::getInstance()->debug('Calcurates API request', $args);
        }

        $args['headers']['X-API-KEY'] = $this->api_key;

        $result = \wp_safe_remote_request($this->api_url.$path, $args);

        if (\is_wp_error($result) || 200 !== \wp_remote_retrieve_response_code($result)) {
            if ('all' === $this->debug_mode || 'errors' === $this->debug_mode) {
                Logger::getInstance()->critical('Calcurates API request error', (array) $result);
            }

            return null;
        }

        $response = \wp_remote_retrieve_body($result);
        $decodedResponse = \json_decode($response, true);

        if (null === $decodedResponse && \JSON_ERROR_NONE !== \json_last_error()) {
            Logger::getInstance()->critical('Can\'t parse the Calcurates API json response: '.\json_last_error_msg(), ['response' => $response]);
        }

        if ('all' === $this->debug_mode) {
            Logger::getInstance()->debug('Calcurates API response', $decodedResponse);
        }

        return $decodedResponse;
    }
}
