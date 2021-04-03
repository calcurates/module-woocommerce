<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Contracts\Rates\RatesExtractorInterface;
use Calcurates\Calcurates\Rates\DTO\RateShopping;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}
class RateShoppingRatesExtractor implements RatesExtractorInterface
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
    public function extract($rate_shopping_rates): array
    {
        foreach ($rate_shopping_rates as $rate) {
            try {
                $this->dtos[] = (new RateShopping($rate));
            } catch (\TypeError $e) {
                $this->logger->debug($e->getMessage());
            }
        }
        
        foreach ($this->dtos as $rate_shopping) {
            foreach ($rate_shopping->carriers as $carrier) {
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
                                'id' => $rate_shopping->id . '_' . $carrier->id . '_' . $services_ids,
                                'label' => $carrier->name . '. ' . $services_names,
                                'cost' => $rate->rate->cost,
                                'tax' => $rate->rate->tax,
                                'message' => $rate_shopping->message . ' ' . $services_messages,
                                'delivery_date_from' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->from : null,
                                'delivery_date_to' => $rate->rate->estimatedDeliveryDate ? $rate->rate->estimatedDeliveryDate->to : null,
                                'priority' => $rate_shopping->priority,
                            ];
                }
            }
        }

        return $this->ready_rates;
    }
}
