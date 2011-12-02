<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Checks caching
 */
class leieCacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * Checks if correct content is stored
     */
    public function testContent()
    {
        $c = new leieCache( 'test' );
        $content = 'content';
        $c->store( $content );

        $this->assertEquals( $content,  $c->getContent() );
    }

    /**
     * Checks if correct content is stored
     */
    public function testContentWithoutStoring()
    {
        $c = new leieCache( 'test' );

        $this->assertEquals( 'content', $c->getContent() );
    }

    /**
     * Checks if a cache is cleared
     */
    public function testClearingCacheByIndexList()
    {
        $c = new leieCache( 'test' );

        $c->setIndexList( array( 'key1' => 'value1',
                                 'key2' => 'value2' ) );
        $c->store( 'content' );

        leieCache::clearByIndexList( array( 'key1' => 'value1' ) );

        $c = new leieCache( 'test' );
        $this->assertFalse( $c->getContent() );
    }

    /**
     * Checks if all cache is cleared
     */
    public function testClearingAllCache()
    {
        $c = new leieCache( 'test1' );
        $c->store( 'content' );

        $c = new leieCache( 'test2' );
        $c->store( 'content' );

        leieCache::clearAll();

        $c = new leieCache( 'test1' );
        $this->assertFalse( $c->getContent() );
        $c = new leieCache( 'test2' );
        $this->assertFalse( $c->getContent() );
    }

}
?>