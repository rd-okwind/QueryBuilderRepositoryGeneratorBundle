<?php

namespace Okwind\QueryBuilderRepositoryGeneratorBundle\Tests\Service;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Nyholm\BundleTest\TestKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Okwind\QueryBuilderRepositoryGeneratorBundle\Generator\RepositoryGenerator;
use Okwind\QueryBuilderRepositoryGeneratorBundle\QueryBuilderRepositoryGeneratorBundle;
use Okwind\QueryBuilderRepositoryGeneratorBundle\Tests\Entity\MyCompositeClass;
use Okwind\QueryBuilderRepositoryGeneratorBundle\Tests\Repository\MyCompositeClassRepositoryBase;

class RepositoryGeneratorTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(DoctrineBundle::class);
        $kernel->addTestBundle(MakerBundle::class);
        $kernel->addTestBundle(TwigBundle::class);
        $kernel->addTestBundle(QueryBuilderRepositoryGeneratorBundle::class);
        $kernel->handleOptions($options);
        $kernel->addTestConfig(__DIR__.'/../config.yml');

        return $kernel;
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideRepositories(): iterable
    {
        yield 'id identifier' => ['MyClassRepository'];
        yield 'composite identifier' => ['MyCompositeClassRepository'];
        yield 'identifier not named id' => ['MyCodeClassRepository'];
        yield 'id field next to a composite identifier' => ['MyIdFieldClassRepository'];
    }

    #[DataProvider('provideRepositories')]
    public function testGenerateFiles(string $repository): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $this->assertTrue($container->has('okwind_qbrg.generator.repository_generator'));

        /** @var RepositoryGenerator */
        $service = $container->get('okwind_qbrg.generator.repository_generator');
        $service->generateFiles();

        $content = \file_get_contents(__DIR__.'/../Repository/'.$repository.'Base.php');

        // keep for dev update
        // \file_put_contents(__DIR__.'/Expected'.$repository.'Base.txt', $content);

        $expectedContent = \file_get_contents(__DIR__.'/Expected'.$repository.'Base.txt');

        $this->assertSame($expectedContent, $content);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function provideIncompleteIdentifiers(): iterable
    {
        yield 'no identifier field set' => [[], 'myClass, date'];
        yield 'one identifier field left out' => [['date' => new \DateTime('2026-01-01')], 'myClass'];
    }

    /**
     * @param array<string, mixed> $identifier
     */
    #[DataProvider('provideIncompleteIdentifiers')]
    public function testExistsThrowsWhenTheIdentifierIsIncomplete(array $identifier, string $missingFields): void
    {
        self::bootKernel();

        $repository = new MyCompositeClassRepositoryBase(self::getContainer()->get('doctrine'));

        // getIdentifierValues() leaves out the identifier fields without value
        $entity = new MyCompositeClass();
        foreach ($identifier as $field => $value) {
            (new \ReflectionProperty(MyCompositeClass::class, $field))->setValue($entity, $value);
        }

        // thrown before any query: the test kernel has no reachable database
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The identifier of '.MyCompositeClass::class.' is incomplete, missing: '.$missingFields);

        $repository->exists($entity);
    }

    /**
     * @param array<string, mixed> $identifier
     */
    #[DataProvider('provideIncompleteIdentifiers')]
    public function testFindOneThrowsWhenTheIdentifierIsIncomplete(array $identifier, string $missingFields): void
    {
        self::bootKernel();

        $repository = new MyCompositeClassRepositoryBase(self::getContainer()->get('doctrine'));

        // thrown before any query: the test kernel has no reachable database
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The identifier of '.MyCompositeClass::class.' is incomplete, missing: '.$missingFields);

        $repository->findOne($identifier);
    }
}
