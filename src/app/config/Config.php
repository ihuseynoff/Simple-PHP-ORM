<?php
/**
 * Author: Ilkin Huseynov
 * E-mail: ilkin@huseynov.me
 * Web: huseynov.me
 * Date: 7/8/18, 02:06
 **/

namespace App\Config;


class AppConfig
{

    /**
     * @var array
     */
    private static $DB_CONFIG = [
        "hostName" => " ",
        "dbName" => " ",
        "userName" => " ",
        "dbPassword" => " ",
    ];


    /**
     * @return array
     */
    public static function DB_CONFIG(): array
    {
        return self::$DB_CONFIG;
    }


}