<?php

declare(strict_types=1);

namespace BehaviorFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Knp\DoctrineBehaviors\Model\Tree;

/**
 * @ORM\Entity(repositoryClass="BehaviorFixtures\ORM\TreeNodeEntityRepository")
 */
class TreeNodeEntity implements Tree\NodeInterface, \ArrayAccess
{
    use Tree\Node;

    public const PATH_SEPARATOR = '/';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    public function __construct($id = null)
    {
        $this->childNodes = new ArrayCollection();
        $this->id = $id;
    }

    public function __toString()
    {
        return (string) $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
