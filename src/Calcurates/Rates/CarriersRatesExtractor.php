<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Calcurates\Rates\DTO\Carrier;
use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class CarriersRatesExtractor implements RatesExtractorInterface
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
    public function extract($carriers): array
    {
        foreach ($carriers as $carrier) {
            try {
                $this->dtos[] = (new Carrier($carrier));
            } catch (\TypeError $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        foreach ($this->dtos as $carrier) {
            foreach ($carrier->rates as $rate) {
                $services_names = [];
                $services_messages = [];
                $services_ids = [];
                foreach ($rate->services as $services) {
                    $services_messages[] = $services->message;
                    $services_ids[] = $services->id;
                    $services_names[] = $services->name;
                }
                        
                $services_messages = \implode('. ', $services_messages);
                $services_ids = \implode('_', $services_ids);
                $services_names = \implode(', ', $services_names);

                $this->ready_rates[] = [
                    'id' => $carrier->id . '_' . $services_ids,
                    'label' => $carrier->name . '. ' . $services_names,
                    'cost' => $rate->rate->cost,
                    'tax' => $rate->rate->tax,
                    'message' => $carrier->message . ' ' . $services_messages,
                    'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                    'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                    'priority' => $carrier->priority,
                ];
            }
        }

        return $this->ready_rates;
    }
}
