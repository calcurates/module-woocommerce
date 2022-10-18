<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

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
            if (!$rate_shopping['success']) {
                if ($rate_shopping['message']) {
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
                        'priority_item' => null,
                        'rate_image' => $rate_shopping['imageUri'],
                    ];
                }
                continue;
            }

            foreach ($rate_shopping['carriers'] as $carrier) {
                if (!$carrier['success']) {
                    continue;
                }
                foreach ($carrier['rates'] as $rate) {
                    if (!$rate['success']) {
                        continue;
                    }

                    $services_names = [];
                    $services_messages = [];
                    $services_ids = [];
                    $services_priority = null;

                    foreach ($rate['services'] as $service) {
                        if ($service['message']) {
                            $services_messages[] = $service['message'];
                        }

                        $services_ids[] = $service['id'];
                        $services_names[] = $service['name'];
                        if (null !== $service['priority']) {
                            $services_priority += $service['priority'];
                        }
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
                        'priority_item' => $services_priority,
                        'rate_image' => $rate_shopping['imageUri'],
                    ];
                }
            }
        }

        return $ready_rates;
    }
}
