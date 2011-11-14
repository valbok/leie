<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Handler to process templace cache block calls
 */
class leieCacheBlockTemplateHandler extends leieCacheBlockHandler
{
    /**
     * Handler type
     *
     * @var (string)
     */
    protected $Type = false;

    /**
     * Returns handler instance
     *
     * @param (string) $name Cache block name
     * @param (array) $paramList Param list
     * @param (string) $type Handler type
     * @return (__CLASS__)
     */
    public static function get( $name, $paramList = array(), $type = false )
    {
        $o = new self( $name, $paramList );
        $o->Type = $type !== false ? $type : self::getDefaultType();

        return $o;
    }

    /**
     * Returns a cache block handler type
     *
     * @return (string)
     */
    protected static function getDefaultType()
    {
        return 'ajax';
    }

    /**
     * Returns supported handler type list
     *
     * @return (array)
     */
    protected static function getTemplateList()
    {
        return array( 'ajax' => 'cache-block/ajax-handler.tpl' );
    }

    /**
     * Returns path to template by handler type
     *
     * @param (string)
     * @return (string)
     */
    protected static function getTemplate( $type )
    {
        $list = self::getTemplateList();
        $default = self::getDefaultType();

        return isset( $list[$type] ) ? $list[$type] : false;
    }

    /*
     * Handles a call from template
     *
     * @param (integer) TTL of a cache
     * @return (string) HTML
     */
    public function handle( $ttl = 0 )
    {
        if ( $this->Type === '' )
        {
            return $this->CacheBlock->process();
        }

        $params = self::encodeParamList( $this->ParamList );

        $template = self::getTemplate( $this->Type );
        if ( !$template )
        {
            throw new leieRunTimeException( 'Could not find a template by the type: ' . $this->Type );
        }

        $tpl = new leieTemplate();
        $tpl->setVariable( 'cache_block_uri', $this->getURI( $ttl ) );

        return $tpl->fetch( $template );
    }

    /**
     * Returns URI to process the cache block
     *
     * @param (int)
     * @return (string)
     */
    protected function getURI( $ttl )
    {
        $params = self::encodeParamList( $this->ParamList );
        $uri = '/action/cache-block/' . $this->Name . '/' . $params . '/' . self::createHash( $this->Name, $params ) . '/' . $ttl;

        return $uri;
    }
}
?>