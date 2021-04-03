<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Calcurates\Rates\DTO\FreeShipping;
use Calcurates\Contracts\Rates\RatesExtractorInterface;
use Calcurates\Utils\Logger;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class FreeShippingRatesExtractor implements RatesExtractorInterface
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

    public function extract(array $rates): array
    {
        // TODO: need refactoring? to clean context from FreeShipping deps
        foreach ($rates as $rate) {
            try {
                $this->dtos[] = (new FreeShipping($rate));
            } catch (\TypeError $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        $ready_rates = array();
        foreach ($this->dtos as $rate) {
            $ready_rates[] = array(
                'id' => $rate->id,
                'label' => $rate->name,
                'cost' => $rate->rate->cost,
                'tax' => $rate->rate->tax ,
                'message' => $rate->message,
                'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                'priority' => $rate->priority,
            );
        }

        return $ready_rates;
    }
}
