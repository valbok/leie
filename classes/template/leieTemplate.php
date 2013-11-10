<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie::template
 */

/**
 * Simplified object to handle templates
 */
class leieTemplate
{
    /**
     * @var array
     */
    protected static $InstanceList = array();

    /**
     * Dir where tpls are located
     *
     * @var string
     */
    protected $Dir = '';

    /**
     * Submitted variable list
     *
     * @var array
     */
    protected $VarList = array();

    /**
     * @param string $dir Where templates are located
     */
    public function __construct( $dir = 'design/templates' )
    {
        $this->Dir = $dir;
    }

    /**
     * @return void
     */
    protected static function setInstance( leieTemplate $o )
    {
        self::$InstanceList[$o->Dir] = $o;
    }

    /**
     * @return void
     */
    protected static function setDefaultInstance( leieTemplate $o )
    {
        self::$InstanceList['default'] = $o;
    }

    /**
     * @return __CLASS__
     */
    protected static function getDefaultInstance()
    {
        return isset( self::$InstanceList['default'] ) ? self::$InstanceList['default'] : false;
    }

    /**
     * @return __CLASS__
     */
    protected static function getInstance( $dir = false )
    {
        return $dir === false ? self::getDefaultInstance() : ( isset( self::$InstanceList[$dir] ) ? self::$InstanceList[$dir] : false );
    }

    /**
     * Wrapper to create the object
     *
     * @return __CLASS__
     */
    public static function get( $dir = false )
    {
        $o = self::getInstance( $dir );
        if ( $o )
        {
            return $o;
        }

        return $dir ? new self( $dir ) : new self();
    }

    /**
     * Sets singelton
     *
     * @return this
     */
    public function setDefault()
    {
        self::setDefaultInstance( $this );

        return $this;
    }

    /**
     * Sets a var
     *
     * @param string
     * @param mixed
     * @return void
     */
    public function setVariable( $name, $value )
    {
        $this->VarList[$name] = $value;
    }

    /**
     * Gets a var
     *
     * @param string
     * @return mixed|null
     */
    public function getVariable( $name )
    {
        return isset( $this->VarList[$name] ) ? $this->VarList[$name] : null;
    }

    /**
     * Processes provided template
     *
     * @return string
     */
    public function fetch( $filename, $ttl = false )
    {
        $tpl = $this->Dir . '/' . $filename;
        if ( !file_exists( $tpl ) )
        {
            throw new leieRunTimeException( "Template '$tpl' does not exist" );
        }

        // @todo: Fix var names. It is due to if $_leie_template_var_name_100500 is passed as a variable
        // it will be overrided by this foreach
        foreach ( $this->VarList as $_leie_template_var_name_100500 => $_leie_template_var_value_100500 )
        {
            $$_leie_template_var_name_100500 = $_leie_template_var_value_100500;
        }

        ob_start();
        $result = include( $tpl );
        return ob_get_clean();
    }

    /**
     * Includes template
     *
     * @return void
     */
    public static function includeTemplate( $uri, $data = array() )
    {
        $tpl = self::get();
        foreach ( $data as $key => $value )
        {
            $tpl->setVariable( $key, $value );
        }

        echo $tpl->fetch( $uri );
    }

    /**
     * Washes the content
     *
     * @return string
     */
    public static function wash( $content, $type = 'xhtml' )
    {
        switch ( $type )
        {
            default:
            case 'xhtml':
            {
                $result = htmlspecialchars( $content );
            } break;

            case 'javascript':
            {
                $result = str_replace( array( "\\", "\"", "'"),
                                       array( "\\\\", "\\042", "\\047" ) , $content );
            } break;
        }

        return $result;
    }
}
?>
