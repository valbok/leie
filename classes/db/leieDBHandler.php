<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Container of database settings
 * Is used to initialize a database connection
 */
class leieDBHandler
{
    /**
     * Initializes db
     *
     * @return (void)
     */
    public static function initialize( $database, $user = 'root', $password = '', $host = 'localhost', $driver = 'mysql'  )
    {
        $db = ezcDbFactory::create( $driver . '://' . $user . ':' . $password . '@' . $host .'/' . $database );
        ezcDbInstance::set( $db );
    }


}

?>
