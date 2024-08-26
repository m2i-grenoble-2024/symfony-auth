<?php

namespace App\Repository;

use PDO;

class Database
{


    public static function connect()
    {
        return new PDO("mysql:host={$_ENV['DATABASE_HOST']};dbname={$_ENV['DATABASE_NAME']};port={$_ENV['DATABASE_PORT']}", $_ENV['DATABASE_USERNAME'], $_ENV['DATABASE_PASSWORD']);
    }
}