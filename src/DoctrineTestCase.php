<?php
namespace MeetNeedz\TestTools;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Class DoctrineTestCase
 *
 * @author Raphael De Freitas <raphael.defreitas@meetneedz.com>
 */
abstract class DoctrineTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        if (!class_exists('Doctrine\ORM\EntityManager')) {
            $this->markTestSkipped(sprintf('The test "%s" is not available : Doctrine ORM is not available.', get_class($this)));
        }

        $configuration = new Configuration();
        $configuration->setMetadataCacheImpl(new ArrayCache());
        $configuration->setQueryCacheImpl(new ArrayCache());
        $configuration->setProxyDir($this->getProxySettings()[0]);
        $configuration->setProxyNamespace($this->getProxySettings()[1]);
        $configuration->setMetadataDriverImpl($configuration->newDefaultAnnotationDriver());

        $connectionSettings = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $this->entityManager = EntityManager::create($connectionSettings, $configuration);

        $entities = $this->getEntities();

        // Creating the schema
        $entityClasses = [];
        foreach ($entities as $entity) {
            if (in_array(get_class($entity), $entityClasses) === false) {
                $entityClasses[] = $this->entityManager->getClassMetadata(get_class($entity));
            }
        }
        $schemaTool = new SchemaTool($this->entityManager);

        $schemaTool->createSchema($entityClasses);

        // Storing the entities
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }

    /**
     * Gets the proxy settings
     *
     * @return array [directory, namespace]
     */
    abstract protected function getProxySettings();

    /**
     * Gets the entities to persist and map
     *
     * @return object[]
     */
    abstract protected function getEntities();
}