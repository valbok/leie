<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 *
 */
class leieModuleResult
{
    /**
     *
     * @var (string)
     */
    public $Result = '';

    /**
     *
     * @var (string)
     */
    public $Title = '';

    /**
     *
     * @var (string)
     */
    public $Path = array();


    /**
     *
     * @var (string)
     */
    protected $Pagelayout = '';

    /**
     * @param (array) $dir Dir where modules are located
     */
    public function __construct( array $data = array() )
    {
        $this->Result = $data['result'];
        $this->Title = isset( $data['title'] ) ? $data['title'] : '';
        $this->Pagelayout = isset( $data['pagelayout'] ) ? $data['pagelayout'] : 'pagelayout.tpl';
        $this->Path = isset( $data['path'] ) ? $data['path'] : array();
    }

    public function handle()
    {
        $tpl = new leieTemplate();
        $tpl->setVariable( 'module_result', $this );

        return $tpl->fetch( $this->Pagelayout );
    }
}
?>