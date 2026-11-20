<?php

namespace Okwind\QueryBuilderRepositoryGeneratorBundle\Tests\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Okwind\QueryBuilderRepositoryGeneratorBundle\Tests\Repository\MyCodeClassRepository;

/**
 * Single identifier, not named "id".
 */
#[ORM\Entity(repositoryClass: MyCodeClassRepository::class)]
class MyCodeClass
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING)]
    private string $code;

    #[ORM\Column(type: Types::STRING)]
    private string $label;
}
