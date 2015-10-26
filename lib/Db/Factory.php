<?php
namespace FMUP\Db;

use FMUP\Logger\LoggerInterface;

class Factory
{
    const DRIVER_PDO = 'Pdo';
    const DRIVER_PDO_MYSQL = 'Pdo\\Mysql';
    const DRIVER_PDO_ODBC = 'Pdo\\Odbc';
    const DRIVER_PDO_SQLSRV = 'Pdo\\SqlSrv';
    const DRIVER_PDO_SQLITE = 'Pdo\\Sqlite';
    const DRIVER_MOCK = 'Mock';

    private static $instance;

    protected function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class();
        }
        return self::$instance;
    }

    /**
     * @param string $driver
     * @param array $params
     * @return DbInterface
     * @throws Exception
     */
    final public function create($driver = self::DRIVER_PDO, $params = array())
    {
        $class = $this->getClassNameForDriver($driver);
        if (!class_exists($class)) {
            throw new Exception('Unable to create ' . $class);
        }
        $instance = new $class($params);
        if (!$instance instanceof DbInterface) {
            throw new Exception('Unable to create ' . $class);
        }
        return $instance;
    }

    /**
     * Get full class name to create
     * @param string $driver
     * @return string
     */
    protected function getClassNameForDriver($driver)
    {
        return 'FMUP\\Db\\Driver\\' . $driver;
    }
}
