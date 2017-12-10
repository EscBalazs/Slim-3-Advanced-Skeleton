<?php namespace Integration;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Query\QueryBuilder;
use ExtendedSlim\App;
use ExtendedSlim\App\Config;
use Doctrine\DBAL\Connection;
use Tests\AbstractTest;

abstract class AbstractIntegrationTest extends AbstractTest
{
    /** @var Container */
    private $container;

    /** @var Connection */
    private $connection;

    public function setUp()
    {
        parent::setUp();

        (new Config())->envSetup();
        $this->container  = (new App())->getContainer();
        $this->connection = $this->container->get(Connection::class);
        $this->connection->beginTransaction();
    }

    /**
     * @throws ConnectionException
     */
    public function tearDown()
    {
        parent::tearDown();

        $this->connection->rollBack();
    }

    /**
     * @return QueryBuilder
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        return $this->connection->createQueryBuilder();
    }

    /**
     * @return Container
     */
    protected function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @param string $class
     *
     * @return object
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function getFromContainer(string $class)
    {
        return $this->container->get($class);
    }

    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->connection;
    }
}
