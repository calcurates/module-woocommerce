<?php

namespace Calcurates\Calcurates\Rates\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Rate extends FlexibleDataTransferObject
{
    /** @var string $currency */
    public $currency;

    /** @var int|float $cost */
    public $cost;

    /** @var int|null $tax */
    public $tax;

    /** @var \Calcurates\Calcurates\Rates\DTO\EstimatedDeliveryDate|null $estimatedDeliveryDate */
    public $estimatedDeliveryDate;
}
