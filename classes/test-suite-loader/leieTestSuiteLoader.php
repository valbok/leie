<?php
/**
 * Suite loader to run the test within leie environment
 *
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

require_once 'autoload.php';

class leieTestSuiteLoader extends PHPUnit_Runner_StandardTestSuiteLoader
{
    /**
     * @reimp
     */
    function load($suiteClassName, $suiteClassFile = '', $syntaxCheck = FALSE)
    {
        return parent::load( $suiteClassName, $suiteClassFile, $syntaxCheck );
    }

    /**
     * @reimp
     */
    public function reload( ReflectionClass $aClass)
    {
        return parent::reload( $aClass );
    }
}


?>