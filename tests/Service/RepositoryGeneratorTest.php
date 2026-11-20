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
}
