<?php

class testTableIncrement extends leieDBObject
{
    const Table = 'increment_table';

    /**
     * @reimp
     */
    public static function definition()
    {
        return array( 'table' => self::Table,
                      'class' => __CLASS__,
                      'keys' => array( 'id' ),
                      'increment_key' => 'id' );
    }

    public static function get( $rows = array() )
    {
        return new self( $rows );
    }

    public static function init( $link )
    {
        mysql_query( 'DROP TABLE IF EXISTS ' . self::Table );
        if ( !mysql_query( "CREATE TABLE " . testTableIncrement::Table . " (
                            id int(11) NOT NULL auto_increment,
                            name varchar(255) default NULL,
                            PRIMARY KEY (id)
                            ) ENGINE=InnoDB;" ) )
        {
            throw new Exception( mysql_errno( $link ) . ': ' . mysql_error( $link ) );
        }
    }
}

class testTable2Keys extends leieDBObject
{
    const Table = '2keys_table';

    /**
     * @reimp
     */
    public static function definition()
    {
        return array( 'table' => self::Table,
                      'class' => __CLASS__,
                      'keys' => array( 'post_id', 'city_id' ),
                      //'increment_key' => 'id'
                      );
    }

    public static function init( $link )
    {
        mysql_query( 'DROP TABLE IF EXISTS ' . self::Table );
        if ( !mysql_query( "CREATE TABLE " . self::Table . " (
                            post_id int(11) NOT NULL default '0',
                            city_id int(11) NOT NULL default '0',
                            name varchar(255) default NULL,
                            PRIMARY KEY (post_id,city_id)
                            ) ENGINE=InnoDB;" ) )
        {
            throw new Exception( mysql_errno( $link ) . ': ' . mysql_error( $link ) );
        }
    }

}

class leieDBObjectTest extends PHPUnit_Framework_TestCase
{
    const Database = 'leie_tmp_database';

    public static function connect()
    {
        static $static = false;
        if ( $static )
        {
            return $static;
        }

        $static = mysql_connect( 'localhost', 'root', '' );

        return $static;
    }

    public static function initDB()
    {
        $link = self::connect();

        mysql_query( "DROP DATABASE " . self::Database );
        mysql_query( "CREATE DATABASE " . self::Database );
        mysql_select_db( self::Database );

        leieDBHandler::initialize( self::Database );
    }

    public function testFetchObjectList()
    {
        self::initDB();
        $link = self::connect();
        testTableIncrement::init( $link );
        mysql_select_db( self::Database );
        mysql_query( 'INSERT INTO ' . testTableIncrement::Table . ' ( name ) VALUES ( \'TEST1\' )' );
        mysql_query( 'INSERT INTO ' . testTableIncrement::Table . ' ( name ) VALUES ( \'TEST2\' )' );

        $l = testTableIncrement::get()->fetchObjectList();
        $this->assertArrayHasKey( '0', $l );
        $this->assertEquals( 'TEST1', $l[0]->getAttribute( 'name' ) );
        $this->assertArrayHasKey( '1', $l );
        $this->assertEquals( 'TEST2', $l[1]->getAttribute( 'name' ) );
    }


    public function testInnerJoin()
    {
        $link = self::connect();
        testTable2Keys::init( $link );
        mysql_query( 'INSERT INTO ' . testTable2Keys::Table . ' ( post_id, city_id, name ) VALUES ( 1, 1, "inner" )' );

        $o = testTableIncrement::get();
        $q = $o->createSelect();
        $q->innerJoin( testTable2Keys::Table, $q->expr->eq( testTableIncrement::Table . '.id', testTable2Keys::Table. '.post_id' ) );

        $l = $o->fetchObjectList( $q );
        $this->assertArrayHasKey( '0', $l );
        $this->assertEquals( 'TEST1', $l[0]->getAttribute( 'name' ) );
    }

    public function testInsertIncrement()
    {
        self::initDB();
        $link = self::connect();
        testTableIncrement::init( $link );

        $c = new testTableIncrement( array( 'name' => 'TEST' ) );
        $c->insert();

        $l = $c->fetchObjectList();
        $this->assertArrayHasKey( '0', $l );
        $this->assertEquals( 'TEST', $l[0]->getAttribute( 'name' ) );
    }

    public function testInsertLastIDIncrement()
    {
        self::initDB();
        $link = self::connect();
        testTableIncrement::init( $link );

        $c = new testTableIncrement( array( 'name' => 'TEST' ) );
        $c->insert();

        $this->assertEquals( 'TEST', $c->getAttribute( 'name' ) );
        $this->assertEquals( '1', $c->getAttribute( 'id' ) );
    }

    public function testUpdateIncrement()
    {
        $l = testTableIncrement::get()->fetchObjectList();

        $l[0]->setAttribute( 'name', 'UPDATED' );
        $l[0]->update();

        $l = testTableIncrement::get()->fetchObjectList();
        $this->assertArrayHasKey( '0', $l );
        $this->assertEquals( 'UPDATED', $l[0]->getAttribute( 'name' ) );
    }

    public function testFetchObjectListANDCondIncrement()
    {
        $o = testTableIncrement::get();
        $q = $o->createSelect( array( array( 'name', 'eq', 'UPDATED2' ),
                                      array( 'name', 'eq', 'UPDATED' ) ) );

        $l = $o->fetchObjectList( $q );
        $this->assertArrayNotHasKey( '0', $l );
    }

    public function testFetchObjectListStringCondIncrement()
    {
        $l = testTableIncrement::get()->fetchObjectList( testTableIncrement::get()->createSelect( 'id = 1' ) );
        $this->assertArrayHasKey( '0', $l );
        $this->assertInstanceOf( 'testTableIncrement', $l[0] );
    }

    public function testFetchObjectListArrayCondIncrement()
    {
        $l = testTableIncrement::get()->fetchObjectList( testTableIncrement::get()->createSelect( array( 'id', 'eq', '1' ) ) );
        $this->assertArrayHasKey( '0', $l );
        $this->assertInstanceOf( 'testTableIncrement', $l[0] );
    }

    public function testFetchObjectListStringORCondIncrement()
    {
        $l = testTableIncrement::get()->fetchObjectList( testTableIncrement::get()->createSelect( 'id = 1 or id = 2' ) );
        $this->assertArrayHasKey( '0', $l );
        $this->assertInstanceOf( 'testTableIncrement', $l[0] );
    }

    public function testFetchObjectIncrement()
    {
        $l = testTableIncrement::get()->fetchObject( array( 'id', 'eq', 1 ) );

        $this->assertInstanceOf( 'testTableIncrement', $l );
    }

    public function testFetchObjectCondStringIncrement()
    {
        $l = testTableIncrement::get()->fetchObject( 'id = 1' );

        $this->assertInstanceOf( 'testTableIncrement', $l );
    }

    public function testDeleteIncrement()
    {
        $l = testTableIncrement::get()->fetchObjectList();
        $l[0]->delete();

        $l = testTableIncrement::get()->fetchObjectList();
        $this->assertArrayNotHasKey( '0', $l );
    }

    public function testInsert2Keys()
    {
        self::initDB();
        $link = self::connect();
        testTable2Keys::init( $link );

        $c = new testTable2Keys( array( 'post_id' => '1', 'city_id' => '2', 'name' => 'TEST' ) );
        $c->insert();

        $l = $c->fetchObjectList();
        $this->assertArrayHasKey( '0', $l );
        $this->assertEquals( '1', $l[0]->getAttribute( 'post_id' ) );
        $this->assertEquals( '2', $l[0]->getAttribute( 'city_id' ) );
        $this->assertEquals( 'TEST', $l[0]->getAttribute( 'name' ) );
    }

    public function testUpdate2Kyes()
    {
        $c = new testTable2Keys();
        $l = $c->fetchObjectList();

        $l[0]->setAttribute( 'name', 'UPDATED' );
        $l[0]->update();

        $l = $c->fetchObjectList();
        $this->assertArrayHasKey( '0', $l );
        $this->assertEquals( 'UPDATED', $l[0]->getAttribute( 'name' ) );
    }

    public function testDelete2Kyes()
    {
        $c = new testTable2Keys();
        $l = $c->fetchObjectList();
        $l[0]->delete();

        $l = $c->fetchObjectList();
        $this->assertArrayNotHasKey( '0', $l );
    }

}


?>
