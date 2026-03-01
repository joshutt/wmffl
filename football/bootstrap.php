<?php
/**
 * @var $ini array
 */

// bootstrap.php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

require_once 'vendor/autoload.php';

// Create a simple default Doctrine ORM configuration for Attributes
// Use Symfony's Entity directory for all entities
$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: array(__DIR__.'/../symfony-app/src/Entity'),
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

// obtain the EntityManager
$entityManager = new EntityManager($connection, $config);

// Map MySQL ENUM columns to string type
$conn = $entityManager->getConnection();
try {
    $conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
} catch (\Doctrine\DBAL\Exception $e) {
    error_log("Error registering enum type mapping: $e");
}

