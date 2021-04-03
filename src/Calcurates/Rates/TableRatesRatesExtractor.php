<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Calcurates\Rates\DTO\TableRate;
use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}
class TableRatesRatesExtractor implements RatesExtractorInterface
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
    public function extract($table_rates): array
    {
        foreach ($table_rates as $rate) {
            try {
                $this->dtos[] = (new TableRate($rate));
            } catch (\TypeError $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        foreach ($this->dtos as $table_rate) {
            foreach ($table_rate->methods as $rate) {
                $this->ready_rates[] = [
                    'id' => $table_rate->id . '_' . $rate->id,
                    'label' => $rate->name,
                    'cost' => $rate->rate->cost,
                    'tax' => $rate->rate->tax,
                    'message' => $table_rate->message,
                    'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                    'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                    'priority' => $table_rate->priority,
                ];
            }
        }

        return $this->ready_rates;
    }
}
