<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Calcurates\Rates\DTO\Carrier;
use Calcurates\Contracts\Rates\RatesExtractorInterface;
use Calcurates\Utils\Logger;

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
     * Logger
     *
     * @var Logger
     */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->dtos = array();
        $this->logger = $logger;
    }

    public function extract(array $carriers): array
    {
        foreach ($carriers as $carrier) {
            try {
                $this->dtos[] = (new Carrier($carrier));
            } catch (\TypeError $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        $ready_rates = array();
        foreach ($this->dtos as $carrier) {
            foreach ($carrier->rates as $rate) {
                $services_names = array();
                $services_messages = array();
                $services_ids = array();
                foreach ($rate->services as $services) {
                    $services_messages[] = $services->message;
                    $services_ids[] = $services->id;
                    $services_names[] = $services->name;
                }

                $services_messages = \implode('. ', $services_messages);
                $services_ids = \implode('_', $services_ids);
                $services_names = \implode(', ', $services_names);

                $ready_rates[] = array(
                    'id' => $carrier->id . '_' . $services_ids,
                    'label' => $carrier->name . '. ' . $services_names,
                    'cost' => $rate->rate->cost,
                    'tax' => $rate->rate->tax,
                    'message' => $carrier->message . ' ' . $services_messages,
                    'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                    'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                    'priority' => $carrier->priority,
                );
            }
        }

        return $ready_rates;
    }
}
