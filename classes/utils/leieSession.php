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
    /**
     * User ID of logged in user
     *
     * @var (int)
     */
    static protected $UserID = 0;

    /**
     * Has session started
     *
     * @var (bool)
     */
    static protected $HasStarted = false;

    /**
     * Has session started
     *
     * @var (bool)
     */
    static protected $TTL = 0;

    /**
     * Starts a session if needed
     *
     * @return (bool)
     */
    public static function start( $cookieTimeout = false )
    {
        if ( self::$HasStarted )
        {
            return false;
        }

        self::$TTL = $cookieTimeout;

        $sessionName = session_name();
        $hasSessionCookie = isset( $_COOKIE[$sessionName] );
        if ( $hasSessionCookie )
        {
            self::setCookieParams( $cookieTimeout );
            return self::forceStart();
        }

        return false;
    }

    /**
     * Set default cookie parameters based (fallback to php.ini settings)
     * Note: this will only have affect when session is created / re-created
     *
     * @param int|false $lifetime Cookie timeout of session cookie
    */
    protected static function setCookieParams( $lifetime = false )
    {
        $params = session_get_cookie_params();

        if ( $lifetime === false )
        {
            $lifetime = $params['lifetime'];
        }

        session_set_cookie_params( $lifetime, '/' );
    }

    /**
     * Starts session
     *
     * @return (bool)
     */
    public static function forceStart()
    {
        session_start();
        return self::$HasStarted = true;
    }

    /*
     * Removes the current session and resets session variables.
     * Note: implicit stops session as well!
     *
     * @return (bool) Depending on if session was removed.
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

    /**
     * Regenerates the session
     *
     * @return (bool)
     */
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
     * Sets user ID to session
     *
     * @return (void)
     */
    static public function setUserID( $userID = 0 )
    {
        self::set( 'current_user_id', $userID );
        if ( $userID )
        {
            setcookie( 'is_logged_in', 'true', time() + self::$TTL, '/' );
        }
        else
        {
            setcookie( 'is_logged_in', '', time() - 3600, '/' );
        }
    }

    /**
     * Returns user ID
     *
     * @return (int)
     */
    public static function getUserID()
    {
        return self::get( 'current_user_id' );
    }

    /**
     * Set session value (wrapper)
     *
     * @return (bool)
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
     * @param string|null $key Return the whole session array if null otherwise the value of $key
     * @param null|mixed $defaultValue Return this if not null and session has not started
     * @return mixed|null $defaultValue if key does not exist, otherwise session value depending on $key
     */
    public static function &get( $key = null, $defaultValue = null )
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