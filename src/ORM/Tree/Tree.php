<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Tree;

use Doctrine\ORM\QueryBuilder;
use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;

trait Tree
{
    /**
     * Constructs a query builder to get all root nodes
     */
    public function getRootNodesQB(string $rootAlias = 't'): QueryBuilder
    {
        return $this->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias . '.materializedPath = :empty')
            ->setParameter('empty', '')
        ;
    }

    /**
     * Returns all root nodes
     *
     * @api
     */
    public function getRootNodes(string $rootAlias = 't'): array
    {
        return $this
            ->getRootNodesQB($rootAlias)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Returns a node hydrated with its children and parents
     *
     * @api
     *
     * @param array  $extraParams To be used in addFlatTreeConditions
     *
     * @return NodeInterface a node
     */
    public function getTree(string $path = '', string $rootAlias = 't', array $extraParams = []): NodeInterface
    {
        $results = $this->getFlatTree($path, $rootAlias, $extraParams);

        return $this->buildTree($results);
    }

    public function getTreeExceptNodeAndItsChildrenQB(NodeInterface $entity, $rootAlias = 't')
    {
        return $this->getFlatTreeQB('', $rootAlias)
            ->andWhere($rootAlias . '.materializedPath NOT LIKE :except_path')
            ->andWhere($rootAlias . '.id != :id')
            ->setParameter('except_path', $entity->getRealMaterializedPath() . '%')
            ->setParameter('id', $entity->getId())
        ;
    }

    /**
     * Extracts the root node and constructs a tree using flat resultset
     *
     * @param iterable|array $results a flat resultset
     */
    public function buildTree($results): NodeInterface
    {
        if (! count($results)) {
            return;
        }

        $root = $results[0];
        $root->buildTree($results);

        return $root;
    }

    /**
     * Constructs a query builder to get a flat tree, starting from a given path
     *
     * @param array  $extraParams To be used in addFlatTreeConditions
     */
    public function getFlatTreeQB(string $path = '', string $rootAlias = 't', array $extraParams = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias . '.materializedPath LIKE :path')
            ->addOrderBy($rootAlias . '.materializedPath', 'ASC')
            ->setParameter('path', $path . '%')
        ;

        $parentId = basename($path);
        if ($parentId) {
            $qb
                ->orWhere($rootAlias . '.id = :parent')
                ->setParameter('parent', $parentId)
            ;
        }

        $this->addFlatTreeConditions($qb, $extraParams);

        return $qb;
    }

    /**
     * Executes the flat tree query builder
     *
     * @param array  $extraParams To be used in addFlatTreeConditions
     *
     * @return array the flat resultset
     */
    public function getFlatTree(string $path, string $rootAlias = 't', array $extraParams = []): array
    {
        return $this
            ->getFlatTreeQB($path, $rootAlias, $extraParams)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * manipulates the flat tree query builder before executing it.
     * Override this method to customize the tree query
     */
    protected function addFlatTreeConditions(QueryBuilder $qb, array $extraParams): void
    {
    }
}
