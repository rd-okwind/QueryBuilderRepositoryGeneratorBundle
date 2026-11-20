<?php

namespace Okwind\QueryBuilderRepositoryGeneratorBundle\Tests\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Okwind\QueryBuilderRepositoryGeneratorBundle\Tests\Repository\MyIdFieldClassRepository;

/**
 * An "id" field next to a composite identifier: findOne() and exists() keep targeting the "id" field.
 */
#[ORM\Entity(repositoryClass: MyIdFieldClassRepository::class)]
class MyIdFieldClass
{
    #[ORM\Column(type: Types::INTEGER, unique: true)]
    private int $id;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: MyClass::class)]
    private MyClass $myClass;

    #[ORM\Id]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $date;
}
