<?php

namespace Okwind\QueryBuilderRepositoryGeneratorBundle\Tests\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Okwind\QueryBuilderRepositoryGeneratorBundle\Tests\Repository\MyCompositeClassRepository;

/**
 * Composite identifier, made of an association and a column.
 */
#[ORM\Entity(repositoryClass: MyCompositeClassRepository::class)]
class MyCompositeClass
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: MyClass::class)]
    private MyClass $myClass;

    #[ORM\Id]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $date;

    #[ORM\Column(type: Types::INTEGER)]
    private int $value;
}
