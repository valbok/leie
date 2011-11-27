<?php
/**
 * @author VaL
 * @file leieTemplate.php
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Object to handle templates
 */
class leieTemplate
{
    /**
     * Submitted variable list
     *
     * @var (string)
     */
    protected $VarList = array();

    /**
     * @param (string) $dir Where templates are located
     */
    public function __construct( $dir = 'design/templates' )
    {
        $this->Dir = $dir;
    }

    /**
     * Wrapper to create the object
     *
     * @return (__CLASS__)
     */
    public static function get( $dir = false )
    {
        return $dir ? new self( $dir ) : new self();
    }

    /**
     * Sets a var
     *
     * @param (string)
     * @param (mixed)
     * @return (void)
     */
    public function setVariable( $name, $value )
    {
        $this->VarList[$name] = $value;
    }

    /**
     * Gets a var
     *
     * @param (string)
     * @return (mixed|null)
     */
    public function getVariable( $name )
    {
        return isset( $this->VarList[$name] ) ? $this->VarList[$name] : null;
    }

    /**
     * Processes provided template
     *
     * @return (string)
     */
    public function fetch( $filename )
    {
        $tpl = $this->Dir . '/' . $filename;
        if ( !file_exists( $tpl ) )
        {
            throw new leieRunTimeException( "Template '$tpl' does not exist" );
        }

        foreach ( $this->VarList as $name => $value )
        {
            $$name = $value;
        }

        ob_start();
        $result = include( $tpl );
        return ob_get_clean();
    }

    /**
     * Includes template
     *
     * @return (void)
     */
    public static function includeTemplate( $uri, $data = array() )
    {
        $tpl = new self();
        foreach ( $data as $key => $value )
        {
            $tpl->setVariable( $key, $value );
        }

        echo $tpl->fetch( $uri );
    }
}
?>