<?php

declare(strict_types=1);

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\MockObject\MockBuilder;

/**
 * @property-read $this \PHPUnit\Framework\TestCase
 */
trait EntityManagerProvider
{
    private $em;

    abstract protected function getUsedEntityFixtures(): void;

    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory
     */
    protected function getEntityManager(?EventManager $evm = null, ?Configuration $config = null, array $conn = []): EntityManager
    {
        if ($this->em !== null) {
            return $this->em;
        }

        $conn = array_merge([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $conn);

        $config = $config === null ? $this->getAnnotatedConfig() : $config;
        $em = EntityManager::create($conn, $config, $evm ?: $this->getEventManager());

        $schema = array_map(function ($class) use ($em) {
            return $em->getClassMetadata($class);
        }, (array) $this->getUsedEntityFixtures());

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema($schema);
        $schemaTool->createSchema($schema);

        return $this->em = $em;
    }

    /**
     * EntityManager mock object together with
     * annotation mapping driver and engine given
     * by DB_ENGINE (pdo_mysql or pdo_pgsql)
     * database in memory
     */
    protected function getDBEngineEntityManager(): \Doctrine\ORM\EntityManager
    {
        if (DB_ENGINE === 'pgsql') {
            return $this->getEntityManager(
                null,
                null,
                [
                    'driver' => 'pdo_pgsql',
                    'host' => DB_HOST,
                    'dbname' => DB_NAME,
                    'user' => DB_USER,
                    'password' => DB_PASSWD,
                ]
            );
        }
        return $this->getEntityManager(
                null,
                null,
                [
                    'driver' => 'pdo_mysql',
                    'host' => DB_HOST,
                    'dbname' => DB_NAME,
                    'user' => DB_USER,
                    'password' => DB_PASSWD,
                ]
            );
    }

    /**
     * Get annotation mapping configuration
     */
    protected function getAnnotatedConfig(): \Doctrine\ORM\Configuration
    {
        // We need to mock every method except the ones which
        // handle the filters
        $configurationClass = 'Doctrine\ORM\Configuration';
        $refl = new \ReflectionClass($configurationClass);
        $methods = $refl->getMethods();

        $mockMethods = [];

        foreach ($methods as $method) {
            if (! in_array($method->name, ['addFilter', 'getFilterClassName', 'addCustomNumericFunction', 'getCustomNumericFunction'], true)) {
                $mockMethods[] = $method->name;
            }
        }

        /** @var MockBuilder $mockBuilder */
        $mockBuilder = $this->getMockBuilder($configurationClass);
        $mockBuilder->addMethods($mockMethods);

        $config = $mockBuilder->getMock();

        $config
            ->expects($this->once())
            ->method('getProxyDir')
            ->will($this->returnValue(TESTS_TEMP_DIR))
        ;

        $config
            ->expects($this->once())
            ->method('getProxyNamespace')
            ->will($this->returnValue('Proxy'))
        ;

        $config
            ->expects($this->once())
            ->method('getAutoGenerateProxyClasses')
            ->will($this->returnValue(true))
        ;

        $config
            ->expects($this->once())
            ->method('getClassMetadataFactoryName')
            ->will($this->returnValue('Doctrine\\ORM\\Mapping\\ClassMetadataFactory'))
        ;

        $mappingDriver = $this->getMetadataDriverImplementation();

        $config
            ->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($mappingDriver))
        ;

        $config
            ->expects($this->any())
            ->method('getDefaultRepositoryClassName')
            ->will($this->returnValue('Doctrine\\ORM\\EntityRepository'))
        ;

        if (class_exists('Doctrine\ORM\Mapping\DefaultQuoteStrategy')) {
            $config
                ->expects($this->any())
                ->method('getQuoteStrategy')
                ->will($this->returnValue(new DefaultQuoteStrategy()))
            ;
        }

        if (class_exists('Doctrine\ORM\Repository\DefaultRepositoryFactory')) {
            $config
                ->expects($this->any())
                ->method('getRepositoryFactory')
                ->will($this->returnValue(new DefaultRepositoryFactory()))
            ;
        }

        $config
            ->expects($this->any())
            ->method('getDefaultQueryHints')
            ->will($this->returnValue([]))
        ;

        return $config;
    }

    /**
     * Creates default mapping driver
     */
    protected function getMetadataDriverImplementation(): \Doctrine\ORM\Mapping\Driver\Driver
    {
        return new AnnotationDriver($_ENV['annotation_reader']);
    }

    /**
     * Build event manager
     */
    protected function getEventManager(): EventManager
    {
        return new EventManager();
    }
}
