<?php
namespace Calcurates\Calcurates\Rates;

class TableRatesExtractor
{

    /**
     * extract rates
     *
     * @param  object $rates
     * @return array
     */
    public function extract($table_rates): array
    {
        $ready_rates = [];

        foreach ($table_rates as $table_rate) {

            if (property_exists($table_rate, 'success') && $table_rate->success) {

                if (property_exists($table_rate, 'methods') && $table_rate->methods && is_array($table_rate->methods)) {

                    foreach ($table_rate->methods as $rate) {

                        if (property_exists($rate, 'success') && $rate->success) {
                            $ready_rates[] = [
                                'id' => $table_rate->id . '_' . $rate->id,
                                'label' => $rate->name,
                                'cost' => $rate->rate->cost,
                                'taxes' => is_numeric($rate->rate->tax) ? [$rate->rate->tax] : '',
                                'message' => $table_rate->message,
                                'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                                'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                                'priority' => $table_rate->priority,
                            ];
                        }

                    }
                }

            }
        }

        return $ready_rates;
    }

}
