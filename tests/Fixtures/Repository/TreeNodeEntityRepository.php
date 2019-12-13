<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Repository;

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\ORM\Tree\TreeTrait;

final class TreeNodeEntityRepository extends EntityRepository
{
    use TreeTrait;
}
