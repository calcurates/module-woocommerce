<?php
namespace Calcurates\Calcurates\Rates;

class FlatRatesExtractor
{

    /**
     * extract rates
     *
     * @param  object $rates
     * @return array
     */
    public function extract($rates): array
    {
        $ready_rates = [];

        foreach ($rates as $rate) {
            if (property_exists($rate, 'success') && $rate->success) {
                $ready_rates[] = [
                    'id' => $rate->id,
                    'label' => $rate->name,
                    'cost' => $rate->rate->cost,
                    'taxes' => is_numeric($rate->rate->tax) ? [$rate->rate->tax] : '',
                    'message' => $rate->message,
                    'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                    'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                    'priority' => $rate->priority,
                ];
            }
        }

        return $ready_rates;
    }

}
