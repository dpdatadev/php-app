<?php /** @noinspection ALL */

/** @noinspection PhpUndefinedClassInspection */

namespace PDOSingleton;
//Basic wrapper/proxy - Singleton PDO
//Documentation: https://phpdelusions.net/pdo/pdo_wrapper
//TODO? - Abstract class Singleton:
//https://stackoverflow.com/questions/3885464/singleton-in-abstract-class-php#:~:text=abstract%20class%20Singleton%20%7B%20private%20static%20%24instances%20%3D,%24class%20%28%24_params%29%3B%20%7D%20return%20self%3A%3A%24instances%20%5B%24class%5D%3B%20%7D%20%7D
use Exception;
use PDO;


class Postgres
{
    protected static $instance = null;

    /**
     * @throws Exception
     */
    public static function instance()
    {
        if (self::$instance === null) {
            $params = parse_ini_file('database-pg.ini');
            if ($params === false) {
                throw new Exception("Error reading database configuration file");
            }

            $opt = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => FALSE,
            );

            $dsn = sprintf("pgsql:host=%s;port=%d;dbname=%s;",
                $params['host'],
                $params['port'],
                $params['database']);

            self::$instance = new PDO($dsn, $params['user'], $params['password'], $opt);
        }
        return self::$instance;
    }

    // Proxy to native PDO methods
    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(self::instance(), $method), $args);
    }

    //Helper function to run prepared statements smoothly
    public static function run($sql, $args = [])
    {
        if (!$args) {
            return self::instance()->query($sql);
        }
        $stmt = self::instance()->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
}