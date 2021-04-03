<?php

namespace Calcurates\Calcurates\Rates\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class EstimatedDeliveryDate extends FlexibleDataTransferObject
{
    /** @var string|null $from */
    public $from;

    /** @var string|null $to */
    public $to;
}
