<?php
declare(strict_types=1);

namespace Neighborhoods\Bakery\Realpath;

/** @codeCoverageIgnore */
class JITerator implements JITeratorInterface
{
    protected $generator;

    public function next(): void
    {
        $this->getGenerator()->next();
    }

    public function current(): string
    {
        return $this->assertValidArrayItemType(
            $this->getGenerator()->current()
        );
    }

    public function valid(): bool
    {
        return $this->getGenerator()->valid();
    }

    public function rewind(): void
    {
        $this->getGenerator()->rewind();
    }

    public function key()
    {
        return $this->getGenerator()->key();
    }

    protected function assertValidArrayItemType(string $realpath): string
    {
        return $realpath;
    }

    protected function getGenerator(): \Generator
    {
        if ($this->generator === null) {
            throw new \LogicException('Generator has not been set.');
        }

        return $this->generator;
    }

    public function setGenerator(\Generator $generator): JITeratorInterface
    {
        if ($this->generator !== null) {
            throw new \LogicException('Generator is already set.');
        }
        $this->generator = $generator;

        return $this;
    }
}
