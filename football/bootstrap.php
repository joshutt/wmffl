<?php
/**
 * @var $ini array
 */

// bootstrap.php
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

require_once 'vendor/autoload.php';

// Create a simple default Doctrine ORM configuration for Attributes
$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: array(__DIR__.'/../src/orm'),
    isDevMode: true,
);

// configure the database connection
$connection = DriverManager::getConnection([
    'driver' => 'pdo_mysql',
    'user' => $ini['userName'],
    'password' => $ini['password'],
    'host' => $ini['host'],
    'dbname' => $ini['dbName']
//    'dbname' => 'wmffl'
], $config);

Type::addType('ynenum', '\WMFFL\enum\YNEnumType');

// obtain the EntityManager
$entityManager = new EntityManager($connection, $config);

$conn = $entityManager->getConnection();
$conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'ynenum');

