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

try {
    Type::addType('ynenum', '\WMFFL\enum\YNEnumType');
} catch (\Doctrine\DBAL\Exception $e) {
    error_log("Error adding type: $e");
}

// obtain the EntityManager
$entityManager = new EntityManager($connection, $config);
error_log('In entity manager');
if (is_null($entityManager)) {
    error_log('EntityManager is null');
} else {
    error_log('EntityManager is not null');
}

$conn = $entityManager->getConnection();
try {
    $conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'ynenum');
} catch (\Doctrine\DBAL\Exception $e) {
    error_log("Error getting connection: $e");
}

