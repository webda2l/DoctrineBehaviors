<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\ORM;

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\Contract\Entity\GeocodableInterface;
use Knp\DoctrineBehaviors\ORM\Geocodable\GeocodableRepositoryTrait;

final class GeocodableEntityRepository extends EntityRepository
{
    use GeocodableRepositoryTrait;

    public function findOneByTitle(string $title): GeocodableInterface
    {
        return $this->findOneBy([
            'title' => $title,
        ]);
    }
}
