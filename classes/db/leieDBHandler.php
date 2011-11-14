<?php

class leieDBHandler
{
    /**
     * Initializes db
     */
    public static function initialize( $database, $user = 'root', $password = '', $host = 'localhost', $driver = 'mysql'  )
    {
        $db = ezcDbFactory::create( $driver . '://' . $user . ':' . $password . '@' . $host .'/' . $database );
        ezcDbInstance::set( $db );
    }


}

?>
