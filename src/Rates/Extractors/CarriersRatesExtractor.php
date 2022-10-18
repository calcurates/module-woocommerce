<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

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
            if (!$carrier['success']) {
                if ($carrier['message']) {
                    $ready_rates[] = [
                        'has_error' => true,
                        'id' => $carrier['id'],
                        'label' => $carrier['name'],
                        'cost' => 0,
                        'tax' => 0,
                        'message' => $carrier['message'],
                        'delivery_date_from' => null,
                        'delivery_date_to' => null,
                        'priority' => $carrier['priority'],
                        'priority_item' => null,
                        'rate_image' => $carrier['imageUri'],
                    ];
                }
                continue;
            }

            foreach ($carrier['rates'] as $rate) {
                if ($rate['success'] || $carrier['message']) {
                    $services_names = [];
                    $services_messages = [];
                    $services_ids = [];
                    $services_priority = null;

                    if ($rate['success']) {
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
                    }

                    $services_messages = \implode('. ', $services_messages);
                    $services_ids = \implode('_', $services_ids);
                    $services_names = \implode(', ', $services_names);

                    $ready_rates[] = [
                        'has_error' => !$rate['success'],
                        'id' => $carrier['id'].'_'.$services_ids,
                        'label' => $carrier['name'].'. '.$services_names,
                        'cost' => $rate['rate']['cost'] ?? 0,
                        'tax' => $rate['rate']['tax'] ?? 0,
                        'message' => $rate['success'] ? $carrier['message'].' '.$services_messages : $carrier['message'],
                        'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                        'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                        'priority' => $carrier['priority'],
                        'priority_item' => $services_priority,
                        'rate_image' => $carrier['imageUri'],
                    ];
                }
            }
        }

        return $ready_rates;
    }
}
