<?php

namespace Calcurates\Calcurates\Rates\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class CarrierService extends FlexibleDataTransferObject
{
    /** @var int $id */
    public $id;

    /** @var string $name */
    public $name;

    /** @var bool $success */
    public $success;

    /** @var string|null $message */
    public $message;
}
