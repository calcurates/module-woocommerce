<?php

namespace Calcurates\Calcurates\Rates\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class TableRateMethod extends FlexibleDataTransferObject
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

    /** @var string|null $imageUri */
    public $imageUri;
}
