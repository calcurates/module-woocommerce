<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Calcurates\Rates\DTO\FlatRate;
use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class FlatRatesRatesExtractor implements RatesExtractorInterface
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
    public function extract($rates): array
    {
        // TODO: need refactoring? to clean context from FlatRate deps
        foreach ($rates as $rate) {
            try {
                $this->dtos[] = (new FlatRate($rate));
            } catch (\TypeError $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        foreach ($this->dtos as $rate) {
            $this->ready_rates[] = [
                    'id' => $rate->id,
                    'label' => $rate->name,
                    'cost' => $rate->rate->cost,
                    'tax' => $rate->rate->tax ,
                    'message' => $rate->message,
                    'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                    'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                    'priority' => $rate->priority,
                ];
        }

        return $this->ready_rates;
    }
}
