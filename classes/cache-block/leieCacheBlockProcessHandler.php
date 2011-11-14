<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Handler to process cache blocks
 */
class leieCacheBlockProcessHandler extends leieCacheBlockHandler
{

    /**
     * Security hash
     *
     * @var (string)
     */
    protected $Hash = false;

    /**
     * Returns handler instance
     *
     * @param (string) $name Cache block name
     * @param (string) $encodedParamList Ecnoded param list
     * @param (string) $hash Security hash
     * @return (__CLASS__)
     */
    public static function get( $name, $encodedParamList, $hash )
    {
        if ( self::createHash( $name, $encodedParamList ) != $hash )
        {
            throw new leieAccessDeniedException( 'Wrong hash: ' . $hash );
        }

        $o = new self( $name, self::decodeParamList( $encodedParamList ) );
        $o->Hash = $hash;

        return $o;
    }

    /**
     * Processes a cache block call
     *
     * @param (integer) TTL of a cache
     * @return (string) HTML
     */
    public function handle( $ttl = 0 )
    {
        if ( $ttl )
        {
            $cache = new leieCache( $this->Hash );
            if ( $cache->exists() and !$cache->isExpired( $ttl ) )
            {
                return $cache->getContent();
            }
        }

        $result = $this->CacheBlock->process();
        if ( $ttl )
        {
            $cache->store( $result );
        }

        return $result;
    }


}
?>