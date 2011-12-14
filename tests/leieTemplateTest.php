<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Checks templates
 */
class leieTemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * Checks is set var works
     */
    public function testSetVar()
    {
        $tpl = new leieTemplate( 'tests/templates' );
        $tpl->setVariable( 'name', 'name' );
        $tpl->setVariable( 'key', 'key' );
        $tpl->setVariable( 'var', 'var' );
        $c = $tpl->fetch( 'name.tpl' );

        $this->assertEquals( 'name/var/key',  $c );
    }

}
?>