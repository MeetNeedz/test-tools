<?php
namespace MeetNeedz\TestTools;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;

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
    public function setUp()
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

        $entitiesClass = [];
        $entities = $this->getEntities();
        foreach ($entities as $entity) {
            if (in_array(get_class($entity), $entitiesClass) === false) {
                $entitiesClass[] = get_class($entity);
            }
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