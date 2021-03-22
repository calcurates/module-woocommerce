<?php
namespace Calcurates\Calcurates\Rates;

class CarrierRatesExtractor
{

    /**
     * extract rates
     *
     * @param  object $rates
     * @return array
     */
    public function extract($carriers): array
    {
        $ready_rates = [];

        foreach ($carriers as $carrier) {

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
                            'id' => $carrier->id . '_' . $services_ids,
                            'label' => $carrier->name . '. ' . $services_names,
                            'cost' => $rate->rate->cost,
                            'tax' => is_numeric($rate->rate->tax) ? $rate->rate->tax : 0,
                            'message' => $carrier->message . ' ' . $services_messages,
                            'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                            'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                            'priority' => $carrier->priority,
                        ];
                    }
                }
            }

        }

        return $ready_rates;
    }

}
