<?php

declare(strict_types=1);

namespace Calcurates\Calcurates\Rates\Extractors;

use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class CarriersRatesExtractor implements RatesExtractorInterface
{
    public function extract(array $carriers): array
    {
        $ready_rates = [];

        foreach ($carriers as $carrier) {
            if (true !== $carrier['success']) {
                continue;
            }

            foreach ($carrier['rates'] as $rate) {
                if (true !== $rate['success']) {
                    continue;
                }

                $services_names = [];
                $services_messages = [];
                $services_ids = [];

                foreach ($rate['services'] as $service) {
                    if ($service['message']) {
                        $services_messages[] = $service['message'];
                    }

                    $services_ids[] = $service['id'];
                    $services_names[] = $service['name'];
                }

                $services_messages = \implode('. ', $services_messages);
                $services_ids = \implode('_', $services_ids);
                $services_names = \implode(', ', $services_names);

                $ready_rates[] = [
                    'id' => $carrier['id'].'_'.$services_ids,
                    'label' => $carrier['name'].'. '.$services_names,
                    'cost' => $rate['rate']['cost'],
                    'tax' => $rate['rate']['tax'] ?: 0,
                    'message' => $carrier['message'].' '.$services_messages,
                    'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                    'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                    'priority' => $carrier['priority'],
                ];
            }
        }

        return $ready_rates;
    }
}
