<?php

namespace Calcurates\Calcurates\Rates\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class RateShoppingCarrierService extends FlexibleDataTransferObject
{
    /** @var \Calcurates\Calcurates\Rates\DTO\Rate $rate */
    public $rate;

    /** @var int $id */
    public $id;

    /** @var string $name */
    public $name;

    /** @var bool $success */
    public $success;

    /** @var string|null $message */
    public $message;
}
