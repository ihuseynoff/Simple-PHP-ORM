<?php
/**
 * Author: Ilkin Huseynov
 * E-mail: ilkin@huseynov.me
 * Web: huseynov.me
 * Date: 7/11/18, 18:58
 **/

namespace App\Core\Database;

use \PDO;
use \PDOException;
use App\Config\AppConfig;

class Connection
{
    private $connection;
    private static $instance;


    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {

        $dbConfig = AppConfig::DB_CONFIG();
        $hostName = $dbConfig['hostName'];
        $dbName = $dbConfig['dbName'];
        $userName = $dbConfig['userName'];
        $dbPassword = $dbConfig['dbPassword'];


        try {
            $this->connection = new PDO("mysql:host=" . $hostName . ";dbname=" . $dbName . ";charset=utf8", $userName, $dbPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            print $e->getMessage();

        }

    }


    // Magic method clone is empty to prevent duplication of connection
    private function __clone()
    {
    }


    public function setConnection()
    {

        return $this->connection;
    }


    public function unsetConnection()
    {

        return null;

    }


}