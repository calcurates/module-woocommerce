<?php
namespace Calcurates\Calcurates;

use Calcurates\Calcurates\CalcuratesClient;
use Calcurates\Calcurates\Rates\Rates;
use Calcurates\Calcurates\RequestsBodyBuilders\RatesRequestBodyBuilder;
use Calcurates\Utils\Logger;

class Calcurates
{

    private $logger;
    private $api_key;
    private $package;
    private $debug_mode;
    private $rates_request_body_builder;
    private $calcurates_client;
    private $rates_extractor;

    public function __construct(string $api_key, string $api_url, $package = [], string $debug_mode)
    {
        $this->logger = new Logger();
        $this->api_key = $api_key;
        $this->package = $package;
        $this->debug_mode = $debug_mode;
        $this->rates_request_body_builder = new RatesRequestBodyBuilder($package);
        $this->calcurates_client = new CalcuratesClient($api_key, $api_url, $debug_mode);
        $this->rates_tools = new Rates();

    }

    /**
     * get_rates
     *
     * @return array
     */
    public function get_rates(): array
    {
        // build body for request
        $rates_request_body = $this->rates_request_body_builder->build();

        // get request results
        $response = $this->calcurates_client->get_rates($rates_request_body);

        if (!$response) {
            return false;
        }

        // extract rates from response
        $this->rates_tools->extract($response);
        $rates = $this->rates_tools->convert_rates_to_wc_rates($this->package);

        return $rates;

    }

}
