<?php

declare(strict_types=1);

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DeletableEntityInherit extends DeletableEntity
{
    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * Returns object name.
     */
    public function getName(): string
    {
        return $this->name;
    }
}
