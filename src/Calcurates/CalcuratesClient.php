<?php
namespace Calcurates\Calcurates;

use Calcurates\Utils\Logger;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

/**
 * Http client for Calcurates API
 */
class CalcuratesClient
{
    /**
     * Logger
     *
     * @var \Calcurates\Utils\Logger
     */
    private $logger;

    /**
     * Calcurates API key for auth
     *
     * @var string
     */
    private $api_key;

    /**
     * Calcurates API URL
     *
     * @var string
     */
    private $api_url;

    /**
     * Debug level mode
     *
     * @var string
     */
    private $debug_mode;

    /**
     * CalcuratesClient Constructor
     *
     * @param string $api_key
     * @param string $api_url
     * @param string $debug_mode
     */
    public function __construct(string $api_key, string $api_url, string $debug_mode)
    {
        $this->logger = new Logger();
        $this->api_key = $api_key;
        $this->api_url = $api_url;
        $this->debug_mode = $debug_mode;
    }

    /**
     * Get rates from Calcurates server
     *
     * @param  array $rates_request_body
     * @return array|null
     */
    public function get_rates(array $rates_request_body): ?array
    {
        $args = array(
            'timeout' => 10,
            'method' => 'POST',
            'headers' => array(
                'X-API-KEY' => '[KEY]',
                'Content-Type' => 'application/json',
            ),
            'body' => \wp_json_encode($rates_request_body),
        );

        if ($this->debug_mode === 'all') {
            $this->logger->debug('Rates request', (array) $args);
        }

        $args['headers']['X-API-KEY'] = $this->api_key;

        if (\filter_var($this->api_url, \FILTER_VALIDATE_URL) === false) {
            $this->logger->critical('Rates request error. Wrong URL');

            return null;
        }

        $result = \wp_safe_remote_request($this->api_url . '/api/woocommerce/rates', $args); // FIXME is it gzip?

        if (\is_wp_error($result) || \wp_remote_retrieve_response_code($result) !== 200) {
            if ($this->debug_mode === 'all' || $this->debug_mode === 'errors') {
                $this->logger->critical('Rates request error', (array) $result);
            }

            return null;
        }

        $response = \json_decode(\wp_remote_retrieve_body($result), true);

        if ($this->debug_mode === 'all') {
            $this->logger->debug('Calcurates rates resnose', (array) $response);
        }

        return $response;
    }
}
