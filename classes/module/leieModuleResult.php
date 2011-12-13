<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Module result.
 * It is used to fetch proper pagelayout and handle content from module.
 */
class leieModuleResult
{
    /**
     * Result content
     *
     * @var (string)
     */
    public $Result = '';

    /**
     * Title of the page
     *
     * @var (string)
     */
    public $Title = '';

    /**
     * Path to the page
     *
     * @var (array)
     */
    public $Path = array();

    /**
     * Main template name
     *
     * @var (string)
     */
    protected $Pagelayout = '';

    /**
     * Page description
     *
     * @var (string)
     */
    public $Description = '';

    /**
     * Page tags
     *
     * @var (string)
     */
    public $Tags = '';

    /**
     * @reimp
     */
    public function __construct( array $data = array() )
    {
        $this->Result = $data['result'];
        $this->Title = isset( $data['title'] ) ? $data['title'] : '';
        $this->Pagelayout = isset( $data['pagelayout'] ) ? $data['pagelayout'] : 'pagelayout.tpl';
        $this->Path = isset( $data['path'] ) ? $data['path'] : array();
        $this->Description = isset( $data['description'] ) ? $data['description'] : false;
        $this->Tags = isset( $data['tags'] ) ? $data['tags'] : false;
    }

    /**
     * Processes pagelayout and returns content of the page
     *
     * @return (string)
     */
    public function handle()
    {
        $tpl = new leieTemplate();
        $tpl->setVariable( 'module_result', $this );

        return $tpl->fetch( $this->Pagelayout );
    }
}
?>