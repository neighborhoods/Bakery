<?php
declare(strict_types=1);

namespace Neighborhoods\Bakery\Baker;

use Neighborhoods\Bakery\Doctrine;
use Neighborhoods\Bakery\Baker;
use Neighborhoods\Bakery\BakerInterface;
use Neighborhoods\Bakery\SearchCriteria;
use Neighborhoods\Bakery\SearchCriteriaInterface;

class Repository implements RepositoryInterface
{
    use Doctrine\DBAL\Connection\Decorator\Repository\AwareTrait;
    use Baker\Builder\Factory\AwareTrait;
    use SearchCriteria\Doctrine\DBAL\Query\QueryBuilder\Builder\Factory\AwareTrait;

    public function createBuilder(): BuilderInterface
    {
        return $this->getBakerBuilderFactory()->create();
    }

    public function get(SearchCriteriaInterface $searchCriteria): MapInterface
    {
        $queryBuilderBuilder = $this->getSearchCriteriaDoctrineDBALQueryQueryBuilderBuilderFactory()->create();
        $queryBuilderBuilder->setSearchCriteria($searchCriteria);
        $queryBuilder = $queryBuilderBuilder->build();
        $queryBuilder->from(BakerInterface::TABLE_NAME)->select('*');
        $records = $queryBuilder->execute()->fetchAll();

        return $baker = $this->createBuilder()->setRecords($records)->build();
    }

    public function save(MapInterface $map): RepositoryInterface
    {
        // Use Doctrine Connection Decorator Repository to save your DAO to storage.

        return $this;
    }
}