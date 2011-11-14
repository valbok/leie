<?php

class leieINI
{
    protected $RequestedURI = false;
    protected $ParamList = array();

    public function __construct( $settings, $params = array() )
    {
        $this->Settings = $settings;
        $this->ParamList = $params;

    }

    public static function get( $settings, $uri )
    {
        $className = $settings['Class'];

        return new $className( $settings, $uri );
    }

    protected function setting( $name, $required = true )
    {
        if ( !isset( $this->Settings[$name] ) )
        {
            if ( $required )
            {
                throw new Exception( 'Setting does not exist ' . $name );
            }

            return false;
        }

        return $this->Settings[$name];
    }

    public function param( $name )
    {
        $paramList = $this->setting( 'Parameters' );

        if ( !is_array( $paramList ) )
        {
            throw new Exception( 'fuck' );
        }

        foreach ( $paramList as $key => $param )
        {
            if ( $param == $name )
            {
                return isset( $this->ParamList[$key] ) ? $this->ParamList[$key] : false;
            }
        }

        return false;
    }

    public function handle()
    {
        return '';
    }
}
?>