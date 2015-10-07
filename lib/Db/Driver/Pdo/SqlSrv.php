<?php
namespace FMUP\Db\Driver\Pdo;

use \FMUP\Db\Driver\Pdo;

class SqlSrv extends Pdo
{
    protected $instance = null;

    protected function getOptions()
    {
        $options = parent::getOptions();
        $charset = $this->getCharset();
        if ($charset == self::CHARSET_UTF8 && defined('\PDO::SQLSRV_ENCODING_UTF8')) {
            $options[constant('\PDO::SQLSRV_ENCODING_UTF8')] = true;
        }
        return $options;
    }

    protected function getDsn()
    {
        $dsn = $this->getDsnDriver() . ':Server={' . $this->getHost() . '}';
        $database = $this->getDatabase();
        if ($database) {
            $dsn .= ';Database={' . $database . '};';
        }
        return $dsn;
    }

    protected function getDsnDriver()
    {
        return 'sqlsrv';
    }
}
