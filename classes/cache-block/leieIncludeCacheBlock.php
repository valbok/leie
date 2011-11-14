<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Handles including of a template
 * @param (string) uri
 * @param (string) resource
 *
 * @return (string)
 */
class leieIncludeCacheBlock extends leieCacheBlock
{
    /**
     * @reimp
     */
    public function process()
    {
        $tpl = new leieTemplate();
        foreach ( $this->getVariableList() as $key => $value )
        {
            $tpl->setVariable( $key, $value );
        }

        return $tpl->fetch( $this->variable( 'uri' ) );
    }
}
?>