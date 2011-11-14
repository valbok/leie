<?php
/**
 * Include this file when you would like to use autoloading
 *
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 * @see bin/generateautoloads.php
 * @todo Fix hardcode var/autoload.php
 */

require 'Base/src/base.php';
$baseEnabled = true;

/**
 * Provides the native autoload functionality
 */
class leieAutoloader
{
    protected static $Classes = null;

    public static function autoload( $className )
    {
        if ( self::$Classes === null )
        {
            self::$Classes = require 'var/autoload.php';
        }

        if ( isset( self::$Classes[$className] ) )
        {
            require( self::$Classes[$className] );
        }
    }
}

spl_autoload_register( array( 'leieAutoloader', 'autoload' ) );
spl_autoload_register( array( 'ezcBase', 'autoload' ) );

?>
