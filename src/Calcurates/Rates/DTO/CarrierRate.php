<?php

namespace Calcurates\Calcurates\Rates\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class CarrierRate extends FlexibleDataTransferObject
{
    /** @var \Calcurates\Calcurates\Rates\DTO\CarrierService[] $services */
    public $services;

    /** @var \Calcurates\Calcurates\Rates\DTO\Rate $rate */
    public $rate;

    /** @var bool $success */
    public $success;

    /** @var string|null $message */
    public $message;
}
