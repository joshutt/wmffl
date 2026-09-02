<?php

namespace App\Doctrine;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use SensitiveParameter;

/**
 * Forces every new DB connection's MySQL session time_zone to APP_TIMEZONE,
 * regardless of which underlying Doctrine driver is in play (pdo_mysql,
 * mysqli, ...).
 *
 * A driverOptions-based approach (e.g. PDO::MYSQL_ATTR_INIT_COMMAND) only
 * works for pdo_mysql - the mysqli driver interprets those numeric option
 * keys differently and throws "Failed to set option". Running the SET as a
 * real SQL statement right after connect works identically for every
 * driver, which is what this middleware does.
 */
class TimezoneMiddleware implements Middleware
{
    public function __construct(private readonly string $timezone)
    {
        // Guard against SQL injection via a misconfigured env var - a valid
        // IANA timezone name (e.g. "America/New_York") only ever contains
        // these characters.
        if (!preg_match('/^[A-Za-z0-9_\/+-]+$/', $this->timezone)) {
            throw new \InvalidArgumentException(sprintf('Invalid APP_TIMEZONE value: "%s"', $this->timezone));
        }
    }

    public function wrap(Driver $driver): Driver
    {
        $timezone = $this->timezone;

        return new class ($driver, $timezone) extends AbstractDriverMiddleware {
            public function __construct(Driver $driver, private readonly string $timezone)
            {
                parent::__construct($driver);
            }

            /**
             * {@inheritDoc}
             */
            public function connect(
                #[SensitiveParameter]
                array $params
            ): Connection {
                $connection = parent::connect($params);
                $connection->exec("SET time_zone = '{$this->timezone}'");

                return $connection;
            }
        };
    }
}
