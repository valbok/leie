<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Class to handle exceptions
 * @example
 *   leieExceptionHandler::add( new leieException( 'test1' ) );
 *   leieExceptionHandler::add( new leieException( 'test2' ), 'Runtime' );
 *   var_dump( leieExceptionHandler::getErrorList() );
 */
class leieExceptionHandler
{
    /**
     * List of errors:
     *   key   is the description of errors
     *   value is an array of exception messages
     *
     * @var (array)
     */
    protected static $ErrorList = array();

    /**
     * Adds exception message to error list
     *
     * @param (epubException) $e
     * @param (string)        $title Group name of exceptions
     * @param (bool)          $log TRUE means the error should be logged
     */
    public static function add( Exception $e, $title = false, $log = true )
    {
        if ( !$title )
        {
            $title = 'An error has occured';
        }

        $error = $e->getErrorMessage( true );

        if ( $log )
        {
            leieLog::writeError( $error );
        }

        self::$ErrorList[$title][] = $e->getErrorMessage( false );
    }

    /**
     * Returns error list
     *
     * @return (array)
     */
    public static function getErrorList()
    {
        return self::$ErrorList;
    }

    /**
     * Returns error message list
     *
     * @return (array)
     */
    public static function getErrorMessageList()
    {
        $errorList = self::getErrorList();
        $result = array();

        foreach ( $errorList as $titleList )
        {
            foreach ( $titleList as $error )
            {
                $result[] = $error;
            }
        }

        return $result;
    }

}

?>
