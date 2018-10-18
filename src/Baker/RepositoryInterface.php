<?php
declare(strict_types=1);

namespace Neighborhoods\Bakery\Baker;

use Neighborhoods\Bakery\BakerInterface;
use Neighborhoods\Bakery\SearchCriteriaInterface;

interface RepositoryInterface
{
    public function createBuilder(): BuilderInterface;

    public function get(SearchCriteriaInterface $searchCriteria): MapInterface;

    public function save(MapInterface $map): RepositoryInterface;
}
