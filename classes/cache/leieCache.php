<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @pakacge leie::cache
 */

/**
 * Class to store content into cache.
 * Supports cluster installation.
 *
 * @example
 *     $cache = new leieCache( 'key' );
 *     $cache->store( 'content' );
 *
 *     $content = $cache->getContent();
 *     $cache->setIndexList( array( 'test' => 1 ) );
 *     leieCache::clearByIndexList( array( 'test' => 1 ) );
 */
class leieCache
{
    /**
     * Cache dir name
     */
    const CacheDir = 'leie-cache';

    /**
     * Dirname where index will be located
     */
    const IndexDir = 'leie-cache-index';

    /**
     * Cached content
     *
     * @var bytes
     */
    protected $Content = false;

    /**
     * @param string $dir Where content is placed
     * @param string $key a key of content
     */
    public function __construct( $key, $dir = false )
    {
        $this->Path = self::getDir( $dir ) . '/' . md5( $key );
    }

    /**
     * Creates index list for current cache.
     * It will create index file named using key=>value with current cache file path.
     * If the file already exist it will append by new value.
     * To clear cache: create index key, find the file, and get the list of cache filepaths which is related to this key.
     * After that purge these files.
     *
     * @param array $indexList array( 'nameID' => 1, 'confirmationNumber' = 2 )
     *
     * @return void
     */
    public function setIndexList( $indexList )
    {
        if ( !$indexList )
        {
            return;
        }

        foreach ( $indexList as $key => $value )
        {
            $indexKey = self::getIndexKey( $key, $value );
            $index = self::fetchIndex( $indexKey );
            $path = basename( $this->Path );
            if ( in_array( $path, $index ) )
            {
                continue;
            }

            $index[] = $path;
            self::storeIndex( $indexKey, $index );
        }
    }

    /**
     * Fetches index by key
     *
     * @param string $value Index key
     * @return bytes
     */
    protected static function fetchIndex( $key )
    {
        $cache = new self( $key, self::IndexDir );
        $list = $cache->exists() ? unserialize( $cache->getContent() ) : array();

        return $list;
    }

    /**
     * Stores index value by
     *
     * @param string $key Index key
     * @param array $valueList What should be stored
     *
     * @return void
     */
    protected static function storeIndex( $key, $valueList )
    {
        $cache = new self( $key, self::IndexDir );
        if ( !$valueList )
        {
            $cache->delete();
            return;
        }

        $cache->store( serialize( $valueList ) );
    }

    /**
     * Returns index key for cache
     *
     * @param string $key Field name
     * @param string $value Its value
     *
     * @return string
     */
    protected static function getIndexKey( $key, $value )
    {
        return $key . '=' . $value;
    }

    /**
     * Returns dir path for cache
     *
     * @return string
     */
    protected static function getDir( $dir )
    {
        $dir = $dir ? '/' . str_replace( '..', '', $dir ) : '';
        return 'var/cache/' . self::CacheDir  . $dir;
    }

    /**
     * Checks for existence of cache file
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists( $this->Path );
    }

    /**
     * Returns modification time of cache file
     *
     * @return int
     */
    public function getModificationTime()
    {
        return filemtime( $this->Path );
    }

    /**
     * Checks if the cache is expired
     *
     * @return bool
     */
    public function isExpired( $ttl )
    {
        return ( $this->getModificationTime() + $ttl ) <= time();
    }

    /**
     * Stores \a $content
     *
     * @return void
     */
    public function store( $content )
    {
        $dir = dirname( $this->Path );
        if ( !file_exists( $dir ) )
        {
            @mkdir( $dir );
        }

        $content = serialize( $content );
        $this->Content = $content;
        file_put_contents( $this->Path, $content );
    }

    /**
     * Fetches content from cache
     *
     * @return bytes
     */
    public function getContent()
    {
        return $this->Content !== false ? unserialize( $this->Content ) : ( $this->exists() ? unserialize( file_get_contents( $this->Path ) ) : false );
    }

    /**
     * Clears current cache
     *
     * @return void
     */
    public function delete()
    {
        @unlink( $this->Path );
        $this->Content = false;
    }

    /**
     * Clears cache by dir/filename
     *
     * @param string $dir
     */
    public static function clearByPath( $path = '' )
    {
        self::deleteByPath( self::getDir( $path ) );
    }

    /**
     * Deletes file or dir
     *
     * @param string Dir or path to filename
     *
     * @return void
     */
    protected static function deleteByPath( $path )
    {
        if ( is_dir( $path ) )
        {
            ezcBaseFile::removeRecursive( $path );
            return;
        }

        unlink( $path );
    }

    /**
     * Clears cache by keys
     *
     * @param array $indexList array( 'nameID' => 1, 'nameID' => 2 )
     *
     * @return void
     */
    public static function clearByIndexList( $indexList = array() )
    {
        foreach ( $indexList as $key => $value )
        {
            $indexKey = self::getIndexKey( $key, $value );
            $index = self::fetchIndex( $indexKey );
            foreach ( $index as $pathKey => $filePath )
            {
                self::clearByPath( $filePath );
                unset( $index[$pathKey] );
            }

            self::storeIndex( $indexKey, $index );
        }
    }

    /**
     * Clears all leie cache
     *
     * @return void
     */
    public static function clearAll()
    {
        self::clearByPath();
    }
}

?>
