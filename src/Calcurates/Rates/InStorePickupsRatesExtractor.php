<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class InStorePickupsRatesExtractor implements RatesExtractorInterface
{

    /**
     * extract rates
     *
     * @param  object $rates
     * @return array
     */
    public function extract($in_store_rates): array
    {
        $ready_rates = [];

        foreach ($in_store_rates as $in_store_rate) {

            if (\property_exists($in_store_rate, 'success') && $in_store_rate->success) {

                if (\property_exists($in_store_rate, 'stores') && $in_store_rate->stores && \is_array($in_store_rate->stores)) {

                    foreach ($in_store_rate->stores as $rate) {

                        if (\property_exists($rate, 'success') && $rate->success) {
                            $ready_rates[] = [
                                'id' => $in_store_rate->id . '_' . $rate->id,
                                'label' => $rate->name,
                                'cost' => $rate->rate->cost,
                                'tax' => \is_numeric($rate->rate->tax) ? $rate->rate->tax : 0,
                                'message' => $in_store_rate->message,
                                'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                                'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                                'priority' => $in_store_rate->priority,
                            ];
                        }

                    }
                }

            }
        }

        return $ready_rates;
    }

}
