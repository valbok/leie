<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Base module class.
 * It is supposed to handle actions
 */
abstract class leieModule
{
    /**
     * Submitted param list
     *
     * @var (array)
     */
    protected $ParamList = array();

    /**
     * @param (array) @params
     */
    public function __construct( $params = array() )
    {
        $this->ParamList = $params;
    }

    /**
     * Returns submitted param
     *
     * @param (string)
     * @param (string)
     * @param (bool)
     * @param (mixed)
     * @return (string)
     */
    protected function getSubmittedVariable( $type, $name, $required = true, $default = false )
    {
        $result = false;
        switch ( $type )
        {
            case 'get':
            {
                $result = isset( $_GET[$name] ) ? $_GET[$name] : false;
            } break;

            case 'post':
            {
                $result = isset( $_POST[$name] ) ? $_POST[$name] : false;
            } break;

            default:
            case 'param':
            {
                $result = $this->hasParam( $name ) ? $this->ParamList[$name] : false;
            } break;

        }

        if ( $result === false )
        {
            if ( $required )
            {
                throw new leieInvalidArgumentException( "[$type] Variable '$name' is required" );
            }

            return $default;
        }

        return $result;
    }

    /**
     * Returns submitted post var
     *
     * @return (string)
     */
    public function getGET( $name, $required = true, $default = false )
    {
        return $this->getSubmittedVariable( 'get', $name, $required, $default );
    }

    /**
     * Checks if get var has been submitted
     *
     * @return (bool)
     */
    public function hasGET( $name )
    {
        return isset( $_GET[$name] );
    }

    /**
     * Returns submitted post var
     *
     * @return (string)
     */
    public function getPOST( $name, $required = true, $default = false )
    {
        return $this->getSubmittedVariable( 'post', $name, $required, $default );
    }

    /**
     * Checks if post var has been submitted
     *
     * @return (bool)
     */
    public function hasPOST( $name )
    {
        return isset( $_POST[$name] );
    }

    /**
     * Returns submitted param
     *
     * @return (string)
     */
    public function getParam( $name, $required = true, $default = false )
    {
        return $this->getSubmittedVariable( 'param', $name, $required, $default );
    }

    /**
     * Checks if param has been submitted
     *
     * @return (bool)
     */
    public function hasParam( $name )
    {
        return ( isset( $this->ParamList[$name] ) and $this->ParamList[$name] !== false );
    }

    /**
     * Main point to module
     *
     * @return (string)
     */
    abstract public function process();

    /**
     * Creates result object
     *
     * @return (leieModuleResult)
     */
    protected static function createResult( array $data = array() )
    {
        return new leieModuleResult( $data );
    }

    /**
     * Redirects to uri
     *
     * @return (void)
     */
    public static function redirectToURI( $uri = '/' )
    {
        throw new leieRedirectToURIException( $uri );
    }
}
?>