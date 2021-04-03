<?php

namespace Calcurates\Calcurates\Rates\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class RateShoppingCarrierRate extends FlexibleDataTransferObject
{
    /** @var \Calcurates\Calcurates\Rates\DTO\RateShoppingCarrierService[] $services */
    public $services;

    /** @var \Calcurates\Calcurates\Rates\DTO\Rate $rate */
    public $rate;

    /** @var bool $success */
    public $success;

    /** @var string|null $message */
    public $message;
}
