<?php
namespace Calcurates\Calcurates\Rates;

class RateShoppingRatesExtractor
{

    /**
     * extract rates
     *
     * @param  object $rates
     * @return array
     */
    public function extract($rate_shopping_rates): array
    {
        $ready_rates = [];

        foreach ($rate_shopping_rates as $rate_shopping) {

            if (property_exists($rate_shopping, 'success') && $rate_shopping->success) {

                if (property_exists($rate_shopping, 'carriers') && $rate_shopping->carriers && is_array($rate_shopping->carriers)) {

                    foreach ($rate_shopping->carriers as $carrier) {

                        if (property_exists($carrier, 'success') && $carrier->success && is_array($carrier->rates)) {

                            foreach ($carrier->rates as $rate) {

                                if (property_exists($rate, 'success') && $rate->success) {

                                    if (property_exists($rate, 'services') && is_array($rate->services)) {

                                        $services_names = [];
                                        $services_messages = [];
                                        $services_ids = [];

                                        foreach ($rate->services as $services) {

                                            if (property_exists($services, 'message') && $services->message) {
                                                $services_messages[] = $services->message;
                                            }
                                            if (property_exists($services, 'id') && $services->id) {
                                                $services_ids[] = $services->id;
                                            }
                                            if (property_exists($services, 'name') && $services->name) {
                                                $services_names[] = $services->name;
                                            }
                                        }
                                    }

                                    $services_messages = implode('. ', $services_messages);
                                    $services_ids = implode('_', $services_ids);
                                    $services_names = implode(', ', $services_names);

                                    $ready_rates[] = [
                                        'id' => $rate_shopping->id . '_' . $carrier->id . '_' . $services_ids,
                                        'label' => $carrier->name . '. ' . $services_names,
                                        'cost' => $rate->rate->cost,
                                        'taxes' => is_numeric($rate->rate->tax) ? [$rate->rate->tax] : '',
                                        'message' => $rate_shopping->message . ' ' . $services_messages,
                                        'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                                        'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                                        'priority' => $rate_shopping->priority,
                                    ];
                                }
                            }
                        }

                    }
                }

            }
        }

        return $ready_rates;
    }

}
