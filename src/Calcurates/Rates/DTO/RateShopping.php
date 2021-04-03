<?php

namespace Calcurates\Calcurates\Rates\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class RateShopping extends FlexibleDataTransferObject
{
    /** @var int $id */
    public $id;

    /** @var string $name */
    public $name;

    /** @var bool $success */
    public $success;

    /** @var string|null $message */
    public $message;

    /** @var int|null $priority */
    public $priority;

    /** @var string|null $imageUri */
    public $imageUri;

    /** @var \Calcurates\Calcurates\Rates\DTO\RateShoppingCarrier[] $carriers */
    public $carriers;
}
