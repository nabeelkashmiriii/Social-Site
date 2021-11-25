<?php

namespace App\Service;

use MongoDB\Client as DB;

class MongodbConn
{

    protected $connection;


// constructer For Database Connection
    public function __construct($collection)
    {
        $this->connection = (new DB)->SocialSite->$collection;
    }


    // Get Conection
    public function getConnection(){
        return $this->connection;
    }
}
