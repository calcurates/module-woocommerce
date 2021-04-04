<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Contracts\Rates\RatesExtractorInterface;
use Calcurates\Utils\Logger;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class CarriersRatesExtractor implements RatesExtractorInterface
{

    /**
     * extract rates
     *
     * @param  array $rates
     * @return array
     */
    public function extract($carriers): array
    {
        $ready_rates = [];

        foreach ($carriers as $carrier) {
            if ($carrier['success'] !== true) {
                continue;
            }
            
            foreach ($carrier['rates'] as $rate) {
                if ($rate['success'] !== true) {
                    continue;
                }

                $services_names = [];
                $services_messages = [];
                $services_ids = [];

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

                $ready_rates[] = [
                            'id' => $carrier['id'] . '_' . $services_ids,
                            'label' => $carrier['name'] . '. ' . $services_names,
                            'cost' => $rate['rate']['cost'],
                            'tax' => $rate['rate']['tax'] ? $rate['rate']['tax']: 0,
                            'message' => $carrier['message'] . ' ' . $services_messages,
                            'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                            'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                            'priority' => $carrier['priority'],
                        ];
            }
        }

        return $ready_rates;
    }
}
