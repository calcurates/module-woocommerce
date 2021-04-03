<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Calcurates\Rates\DTO\InStorePickup;
use Calcurates\Contracts\Rates\RatesExtractorInterface;
use Calcurates\Utils\Logger;

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
     * Logger
     *
     * @var Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->dtos = array();
        $this->logger = $logger;
    }

    public function extract(array $in_store_rates): array
    {
        foreach ($in_store_rates as $in_store_rate) {
            try {
                $this->dtos[] = (new InStorePickup($in_store_rate));
            } catch (\TypeError $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        $ready_rates = array();
        foreach ($in_store_rates as $in_store_rate) {
            foreach ($in_store_rate->stores as $rate) {
                $ready_rates[] = array(
                    'id' => $in_store_rate->id . '_' . $rate->id,
                    'label' => $rate->name,
                    'cost' => $rate->rate->cost,
                    'tax' => $rate->rate->tax,
                    'message' => $in_store_rate->message,
                    'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                    'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                    'priority' => $in_store_rate->priority,
                );
            }
        }

        return $ready_rates;
    }
}
