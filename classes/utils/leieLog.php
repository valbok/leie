<?php


class leieLog
{
    const ERROR_FILE = 'var/log/error.log';
    const DEBUG_FILE = 'var/log/debug.log';
    const NOTICE_FILE = 'var/log/notice.log';

    /**
     * Append error text to log
     */
    public static function writeError( $text )
    {
        return self::writeFile( self::ERROR_FILE, $text );
    }

    /**
     * Appends simple/debug text to log
     */
    public static function writeDebug( $text )
    {
        return self::writeFile( self::DEBUG_FILE, $text );
    }

    /**
     * Appends simple/debug text to log
     */
    public static function writeNotice( $text )
    {
        return self::writeFile( self::NOTICE_FILE, $text );
    }

    /**
     * Writes \a $string to \a $fileName file
     */
    protected static function writeFile( $fileName, $string )
    {
        $dir = dirname( $fileName );
        if ( !file_exists( $dir ) )
        {
            mkdir( $dir );
        }

        $time = date( 'M j Y H:i:s' );
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HOSTNAME'];

        $notice = "[ " . $time . " ] [" . $ip . ":" . session_id() . "] " . $string . "\n";
        if ( !file_put_contents( $fileName, $notice ) )
        {
            return false;
        }

        return true;
    }
}

?>
