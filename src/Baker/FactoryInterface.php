<?php
declare(strict_types=1);

namespace Neighborhoods\Bakery\Baker;

use Neighborhoods\Bakery\BakerInterface;

/** @codeCoverageIgnore */
interface FactoryInterface
{
    public function create(): BakerInterface;
}
