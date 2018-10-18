<?php
declare(strict_types=1);

namespace Neighborhoods\Bakery\Baker;

use Neighborhoods\Bakery\BakerInterface;

/** @codeCoverageIgnore */
class Factory implements FactoryInterface
{
    use AwareTrait;

    public function create(): BakerInterface
    {
        return clone $this->getBaker();
    }
}
