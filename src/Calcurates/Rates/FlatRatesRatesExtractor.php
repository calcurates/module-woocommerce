<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Contracts\Rates\RatesExtractorInterface;
use Calcurates\Utils\Logger;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class FlatRatesRatesExtractor implements RatesExtractorInterface
{

    /**
     * extract rates
     *
     * @param  array $rates
     * @return array
     */
    public function extract($rates): array
    {
        $ready_rates = array();

        foreach ($rates as $rate) {
            if ($rate['success'] !== true) {
                continue;
            }
            
            $ready_rates[] = array(
                'id' => $rate['id'],
                'label' => $rate['name'],
                'cost' => $rate['rate']['cost'],
                'tax' => $rate['rate']['tax'] ? $rate['rate']['tax']: 0,
                'message' => $rate['message'],
                'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                'priority' => $rate['priority'],
            );
        }

        return $ready_rates;
    }
}
