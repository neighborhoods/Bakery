<?php
declare(strict_types=1);

namespace Neighborhoods\Bakery\Baker;

use Neighborhoods\Bakery\BakerInterface;

/** @codeCoverageIgnore */
trait AwareTrait
{
    protected $NeighborhoodsBakeryBaker;

    public function setBaker(BakerInterface $baker): self
    {
        if ($this->hasBaker()) {
            throw new \LogicException('NeighborhoodsBakeryBaker is already set.');
        }
        $this->NeighborhoodsBakeryBaker = $baker;

        return $this;
    }

    protected function getBaker(): BakerInterface
    {
        if (!$this->hasBaker()) {
            throw new \LogicException('NeighborhoodsBakeryBaker is not set.');
        }

        return $this->NeighborhoodsBakeryBaker;
    }

    protected function hasBaker(): bool
    {
        return isset($this->NeighborhoodsBakeryBaker);
    }

    protected function unsetBaker(): self
    {
        if (!$this->hasBaker()) {
            throw new \LogicException('NeighborhoodsBakeryBaker is not set.');
        }
        unset($this->NeighborhoodsBakeryBaker);

        return $this;
    }
}
