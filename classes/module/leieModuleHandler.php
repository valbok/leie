<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Class to handle modules. It points uri to specified module.
 *
 * modules/user/login/module.php:
 *      return array( 'class' => 'leieNAMEModule',
 *                    'parameters' => array( 'Param1', 'Param2' ) )
 *
 */
class leieModuleHandler
{
    /**
     * Directory name where modules are located
     *
     * @var (string)
     */
    protected $Dir = false;

    /**
     * File name of module settings
     *
     * @var (string)
     */
    protected $ModuleFileName = false;

    /**
     * @param (string) $dir Dir where modules are located
     * @param (string) $moduleFileName File where settings are located:
     */
    public function __construct( $dir, $moduleFileName = 'module.php' )
    {
        $this->Dir = $dir;
        $this->ModuleFileName = $moduleFileName;
    }

    /**
     * Creates the object
     *
     * @return (__CLASS__)
     */
    public static function get( $dir, $moduleFileName = 'module.php' )
    {
        return new self( $dir, $moduleFileName );
    }

    /**
     * Returns module file name
     *
     * @param (string)
     * @return (string)
     */
    protected function findModuleFileName( $uri )
    {
        $uriFileName = $uri ? $uri . '/' : '';
        $filename = $this->Dir . '/' . $uriFileName . $this->ModuleFileName;
        if ( !file_exists( $filename ) )
        {
            if ( !$uriFileName )
            {
                throw new leieRunTimeException( 'Root module does not exist' );
            }

            $a = explode( '/', $uri );
            array_pop( $a );

            return $this->findModuleFileName( implode( '/', $a ) );
        }

        return $filename;
    }

    /**
     * Returns submitted param list
     *
     * @return (array)
     */
    protected function getSubmittedParamList( $uri, $filename )
    {
        if ( $uri == '' )
        {
            return array();
        }

        if ( $uri[strlen( $uri ) - 1] != '/' )
        {
            $uri .= '/';
        }

        $moduleName = str_replace( $this->ModuleFileName, '', str_replace( $this->Dir . '/', '', $filename ) );
        $result = str_replace( $moduleName, '', $uri );
        if ( !$result )
        {
            return array();
        }

        return explode( '/', $result );
    }

    /**
     * Returns param list that is passed to module
     *
     * @return (array) Key is the name of param. Value - its value. FALSE means no params have ben submitted
     */
    protected static function createModuleParamList( array $definition = array(), array $submitted = array() )
    {
        $result = array();
        foreach ( $definition as $key => $name )
        {
            $result[$name] = isset( $submitted[$key] ) ? $submitted[$key] : false;
        }

        return $result;
    }

    /**
     * Returns module according to uri
     *
     * @param (string)
     * @return (leieModule)
     */
    public function getModule( $uri )
    {
        $uri = self::cleanURI( $uri );
        $filename = $this->findModuleFileName( $uri );
        $settings = include( $filename );
        $class = $settings['class'];

        $defParamList = ( isset( $settings['parameters'] ) and is_array( $settings['parameters'] ) ) ? $settings['parameters'] : array();
        $paramList = self::createModuleParamList( $defParamList, $this->getSubmittedParamList( $uri, $filename ) );

        return new $class( $paramList );
    }

    /**
     * Removes unneeded data from uri
     *
     * @return (string)
     */
    protected static function cleanURI( $uri )
    {
        return preg_replace( '/(^\/)|(\/$)/', '', $uri );
    }
}
?>