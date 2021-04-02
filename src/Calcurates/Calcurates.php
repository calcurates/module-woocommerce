<?php
namespace Calcurates\Calcurates;

use Calcurates\Calcurates\CalcuratesClient;
use Calcurates\Calcurates\Rates\Rates;
use Calcurates\Calcurates\RequestsBodyBuilders\RatesRequestBodyBuilder;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class Calcurates
{
    private $rates_request_body_builder;
    private $calcurates_client;
    private $rates_tools;

    public function __construct(CalcuratesClient $calcurates_client, RatesRequestBodyBuilder $rates_request_body_builder, Rates $rates_tools)
    {
        $this->rates_request_body_builder = $rates_request_body_builder;
        $this->calcurates_client = $calcurates_client;
        $this->rates_tools = $rates_tools;
    }

    /**
     * get_rates
     *
     * @return array
     */
    public function get_rates()
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
        $this->rates_tools->apply_tax_mode();
        $rates = $this->rates_tools->convert_rates_to_wc_rates();

        return $rates;
    }
}
