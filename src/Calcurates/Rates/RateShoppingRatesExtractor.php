<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}
class RateShoppingRatesExtractor implements RatesExtractorInterface
{

    /**
     * extract rates
     *
     * @param  array $rates
     * @return array
     */
    public function extract($rate_shopping_rates): array
    {
        $ready_rates = array();

        foreach ($rate_shopping_rates as $rate_shopping) {
            if ($rate_shopping['success'] !== true) {
                continue;
            }
            
            foreach ($rate_shopping['carriers'] as $carrier) {
                if ($carrier['success'] !== true) {
                    continue;
                }
                foreach ($carrier['rates'] as $rate) {
                    if ($rate['success'] !== true) {
                        continue;
                    }

                    $services_names = array();
                    $services_messages = array();
                    $services_ids = array();

                    foreach ($rate['services'] as $services) {
                        if ($services['message']) {
                            $services_messages[] = $services['message'];
                        }

                        $services_ids[] = $services['id'];
                        $services_names[] = $services['name'];
                    }
                    

                    $services_messages = \implode('. ', $services_messages);
                    $services_ids = \implode('_', $services_ids);
                    $services_names = \implode(', ', $services_names);

                    $ready_rates[] = array(
                        'id' => $rate_shopping['id'] . '_' . $carrier['id'] . '_' . $services_ids,
                        'label' => $carrier['name'] . '. ' . $services_names,
                        'cost' => $rate['rate']['cost'],
                        'tax' => $rate['rate']['tax'] ? $rate['rate']['tax']: 0,
                        'message' => $rate_shopping['message'] . ' ' . $services_messages,
                        'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                        'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                        'priority' => $rate_shopping['priority'],
                    );
                }
            }
        }

        return $ready_rates;
    }
}
