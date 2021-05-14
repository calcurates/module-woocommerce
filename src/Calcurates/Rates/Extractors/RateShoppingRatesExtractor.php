<?php

declare(strict_types=1);

namespace Calcurates\Calcurates\Rates\Extractors;

use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class RateShoppingRatesExtractor implements RatesExtractorInterface
{
    public function extract(array $rate_shopping_rates): array
    {
        $ready_rates = [];

        foreach ($rate_shopping_rates as $rate_shopping) {
            if (!$rate_shopping['success'] && $rate_shopping['message']) {
                // fake rate if current table rates conditions for all Shipping Option Item were not met
                $ready_rates[] = [
                    'has_error' => true,
                    'id' => $rate_shopping['id'],
                    'label' => $rate_shopping['name'],
                    'cost' => 0,
                    'tax' => 0,
                    'message' => $rate_shopping['message'],
                    'delivery_date_from' => null,
                    'delivery_date_to' => null,
                    'priority' => $rate_shopping['priority'],
                    'rate_image' => $rate_shopping['imageUri']
                ];
            }

            if (!$rate_shopping['success']) {
                continue;
            }

            foreach ($rate_shopping['carriers'] as $carrier) {
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
                        'has_error' => false,
                        'id' => $rate_shopping['id'].'_'.$carrier['id'].'_'.$services_ids,
                        'label' => $carrier['name'].'. '.$services_names,
                        'cost' => $rate['rate']['cost'] ?? 0,
                        'tax' => $rate['rate']['tax'] ?? 0,
                        'message' => $rate_shopping['message'].' '.$services_messages,
                        'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                        'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                        'priority' => $rate_shopping['priority'],
                        'rate_image' => $rate_shopping['imageUri']

                    ];
                }
            }
        }

        return $ready_rates;
    }
}
