<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Calcurates\Rates\DTO\TableRate;
use Calcurates\Contracts\Rates\RatesExtractorInterface;
use Calcurates\Utils\Logger;

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

    public function extract(array $table_rates): array
    {
        foreach ($table_rates as $rate) {
            try {
                $this->dtos[] = (new TableRate($rate));
            } catch (\TypeError $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        $ready_rates = array();
        foreach ($this->dtos as $table_rate) {
            foreach ($table_rate->methods as $rate) {
                $ready_rates[] = array(
                    'id' => $table_rate->id . '_' . $rate->id,
                    'label' => $rate->name,
                    'cost' => $rate->rate->cost,
                    'tax' => $rate->rate->tax,
                    'message' => $table_rate->message,
                    'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                    'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                    'priority' => $table_rate->priority,
                );
            }
        }

        return $ready_rates;
    }
}
