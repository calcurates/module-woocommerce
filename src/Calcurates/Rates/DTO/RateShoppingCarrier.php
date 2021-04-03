<?php

namespace Calcurates\Calcurates\Rates\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class RateShoppingCarrier extends FlexibleDataTransferObject
{
    /** @var int $id */
    public $id;

    /** @var bool $success */
    public $success;

    /** @var string|null $message */
    public $message;

    /** @var string $name */
    public $name;

    /** @var \Calcurates\Calcurates\Rates\DTO\RateShoppingCarrierRate[] $rates */
    public $rates;
}
