<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Calcurates\Rates\DTO\InStorePickup;
use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class InStorePickupsRatesExtractor implements RatesExtractorInterface
{
    /**
     * rates dtos
     *
     * @var array
     */
    private $dtos;
    
    /**
     * prepared rates array
     *
     * @var array
     */
    private $ready_rates;

    /**
     * Logger
     *
     * @var \Calcurates\Utils\Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param \Calcurates\Utils\Logger $logger
     */
    public function __construct($logger)
    {
        $this->dtos = [];
        $this->ready_rates = [];
        $this->logger = $logger;
    }

    /**
     * extract rates
     *
     * @param  object $rates
     * @return array
     */
    public function extract($in_store_rates): array
    {
        foreach ($in_store_rates as $in_store_rate) {
            try {
                $this->dtos[] = (new InStorePickup($in_store_rate));
            } catch (\TypeError $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        foreach ($in_store_rates as $in_store_rate) {
            foreach ($in_store_rate->stores as $rate) {
                $this->ready_rates[] = [
                    'id' => $in_store_rate->id . '_' . $rate->id,
                    'label' => $rate->name,
                    'cost' => $rate->rate->cost,
                    'tax' => $rate->rate->tax,
                    'message' => $in_store_rate->message,
                    'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                    'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                    'priority' => $in_store_rate->priority,
                ];
            }
        }

        return $this->ready_rates;
    }
}
