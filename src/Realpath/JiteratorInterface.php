<?php
declare(strict_types=1);

namespace Neighborhoods\Bakery\Realpath;

/** @codeCoverageIgnore */
interface JITeratorInterface extends \Iterator
{
    public function current(): string;

    public function setGenerator(\Generator $generator): JITeratorInterface;
}
