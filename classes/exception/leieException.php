<?php
/**
 * @author vd
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Base exception
 */
class leieException extends Exception
{
    /**
     * Contains debug information
     *
     * @var (string)
     */
    protected $DebugMessage = '';

    /**
     * @param (string) $errorMessage
     * @param (string) $debugMessage
     */
    public function __construct( $errorMessage, $debugMessage = '' )
    {
        parent::__construct( $errorMessage );
        $this->setDebugMessage( $debugMessage );
    }

    /**
     * Gets the Exception's error message
     *
     * @param (bool) $showDebug Defines to include debug messages to result error
     *
     * @return (string)
     *
     * @TODO Check if debug is needed by default
     */
    public function getErrorMessage( $showDebug = true )
    {
        return $showDebug ? '[' . get_class( $this ) . ']: ' . $this->getMessage() . "\n" . $this->getDebugMessage() : $this->getMessage();
    }

    /**
     * Returns debug message
     *
     * @return (string)
     */
    protected function getDebugMessage()
    {
       return $this->DebugMessage . $this->getTraceAsString();
    }

    /**
     * Sets debug message
     */
    protected function setDebugMessage( $message )
    {
        $this->DebugMessage = $message . "\n\n";
    }
}


/**
 * If wrong or missed argument was passed
 */
class leieInvalidArgumentException extends leieException
{
}

/**
 * If an object was not found
 */
class leieObjectNotFoundException extends leieException
{
}

/**
 * If a user does not have access
 */
class leieAccessDeniedException extends leieException
{
}

/**
 * Is used when redirect should be processed
 */
class leieRedirectToURIException extends leieException
{
}

/**
 * If solr client throws an exception
 */
class leieSolrException extends leieException
{
}

/**
 * If any other cause not expressible by another exception
 */
class leieRunTimeException extends leieException
{
}

?>
