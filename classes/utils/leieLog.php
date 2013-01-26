<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Logger
 */
class leieLog
{
    static $ErrorFile = 'var/log/error.log';
    static $DebugFile = 'var/log/debug.log';
    static $NoticeFile = 'var/log/notice.log';

    /**
     * Inits file names
     *
     * @param (string)
     * @param (string)
     * @param (string)
     */
    public function __construct( $errorFile = false, $debugFile = false, $noticeFile = false )
    {
        self::$ErrorFile = $errorFile ? $errorFile : 'var/log/error.log';
        self::$DebugFile = $debugFile ? $debugFile : 'var/log/debug.log';
        self::$NoticeFile = $noticeFile ? $noticeFile : 'var/log/notice.log';
    }

    /**
     * Append error text to log
     *
     * @return (bool)
     */
    public static function writeError( $text )
    {
        return self::writeFile( self::$ErrorFile, $text );
    }

    /**
     * Appends simple/debug text to log
     *
     * @return (bool)
     */
    public static function writeDebug( $text )
    {
        return self::writeFile( self::$DebugFile, $text );
    }

    /**
     * Appends simple/debug text to log
     *
     * @return (bool)
     */
    public static function writeNotice( $text )
    {
        return self::writeFile( self::$NoticeFile, $text );
    }

    /**
     * Writes \a $string to \a $fileName file
     *
     * @return (bool)
     * @todo Add rotation
     */
    protected static function writeFile( $fileName, $string )
    {
        $dir = dirname( $fileName );
        if ( !file_exists( $dir ) )
        {
            mkdir( $dir );
        }

        $time = date( 'M j Y H:i:s' );
        $ip = self::getCurrentIP();
        $port = $_SERVER['SERVER_PORT'];

        $notice = "[ " . $time . " ] [" . $_SERVER['REQUEST_METHOD'] . ":" . $port . "] [" . $ip . "] [" . leieSession::getUserID() . ":" . session_id() . "] [" . $_SERVER['REQUEST_URI'] . "]\n" . $string . "\n";
        if ( !file_put_contents( $fileName, $notice, FILE_APPEND ) )
        {
            return false;
        }

        return true;
    }

    /**
     * Returns ip uf current session
     *
     * @return (string)
     */
    public static function getCurrentIP()
    {
        return isset( $_SERVER['HTTP_X_REAL_IP'] ) ? $_SERVER['HTTP_X_REAL_IP'] : ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HOSTNAME'] );
    }
}

?>
