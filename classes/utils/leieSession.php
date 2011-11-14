<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Class to handle sessions
 */
class leieSession
{
    static protected $UserID = 0;
    static protected $HasStarted = false;

    public static function start( $cookieTimeout = false )
    {
        if ( self::$HasStarted )
        {
            return false;
        }

        $sessionName = session_name();
        $hasSessionCookie = isset( $_COOKIE[ $sessionName ] );
        if ( $hasSessionCookie )
        {
            return self::forceStart();
        }

        return false;
    }

    public static function forceStart()
    {
        session_start();
        return self::$HasStarted = true;
    }

    /*
     * Removes the current session and resets session variables.
     * Note: implicit stops session as well!
     *
     * @since 4.1
     * @return bool Depending on if session was removed.
     */
    static public function stop()
    {
        if ( !self::$HasStarted )
        {
             return false;
        }

        session_write_close();
        self::$HasStarted = false;
        return true;
    }

    public static function regenerate()
    {
        if ( !self::$HasStarted )
        {
            return self::forceStart();
        }

        session_regenerate_id();
        return true;
    }

    /**
     *
     */
    static public function setUserID( $userID = 0 )
    {
        self::set( 'current_user_id', $userID );
    }

    /**
     *
     */
    public static function getUserID()
    {
        return self::get( 'current_user_id' );
    }

    /**
     * Set session value (wrapper)
     *
     * @since 4.4
     * @param string $key
     * @return bool
     */
    static public function set( $key, $value )
    {
        if ( self::$HasStarted === false )
        {
            self::start();
        }

        $_SESSION[ $key ] = $value;
        return true;
    }

    /**
     * Get session value (wrapper)
     *
     * @since 4.4
     * @param string|null $key Return the whole session array if null otherwise the value of $key
     * @param null|mixed $defaultValue Return this if not null and session has not started
     * @return mixed|null $defaultValue if key does not exist, otherwise session value depending on $key
     */
    static public function &get( $key = null, $defaultValue = null )
    {
        if ( self::$HasStarted === false )
        {
            if ( $defaultValue !== null )
                return $defaultValue;
            self::start();
        }

        if ( $key === null )
            return $_SESSION;

        if ( isset( $_SESSION[ $key ] ) )
            return $_SESSION[ $key ];

        return $defaultValue;
    }
}
?>