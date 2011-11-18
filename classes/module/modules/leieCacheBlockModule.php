<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Handles cache block ajax requests
 */
class leieCacheBlockModule extends leieModule
{
    /**
     * @reimp
     */
    public function process()
    {
        $name = $this->getParam( 'Name' );;
        $paramList = $this->getParam( 'ParamList' );
        $hash = $this->getParam( 'Hash' );
        $ttl = $this->getParam( 'TTL' );

        try
        {
            $result = leieCacheBlockProcessHandler::get( $name, $paramList, $hash )->handle( $ttl );
        }
        catch ( leieException $e )
        {
            header( 'HTTP/1.1 500 Internal Server Error' );
            leieExceptionHandler::add( $e, "Could not handle the cache-block '$name'" );
        }

        $errorList = leieExceptionHandler::getErrorMessageList();

        echo $errorList ? implode( "\n<br/>", $errorList ) : $result;
        exit;
    }
}
?>