<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Base cache block object
 */
abstract class leieCacheBlock
{
    /**
     * A list with submitted parameters
     *
     * @var (array)
     */
    protected $ParamList = array();

    /**
     * @param (array)
     */
    public function __construct( $paramList = array() )
    {
        $this->ParamList = $paramList;
    }

    /**
     * @param (array)
     * @return (__CLASS__)
     */
    public static function get( $paramList = array() )
    {
        return new self( $paramList );
    }

    /**
     * Returns submitted variable
     *
     * @param (string) $name Variable name
     * @param (bool) $isRequired Checks if an exception should be thrown if variable does not exist
     * @param (mixed) $default Default value of variable
     * @return (mixed)
     */
    protected function variable( $name, $isRequired = true, $default = false )
    {
        if ( !isset( $this->ParamList[$name] ) )
        {
            if ( $isRequired )
            {
                throw new leieInvalidArgumentException( 'Variable "' . $name . '" does not exist' );
            }

            return $default;
        }

        return $this->ParamList[$name];
    }

    /**
     * Processes a cache block
     *
     * @return (string)
     */
    public function process()
    {
        return '';
    }

    /**
     * Returns submitted variable list
     *
     * @return (mixed)
     */
    public function getVariableList()
    {
        return $this->ParamList;
    }
}
?>