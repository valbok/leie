<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Handles ajax requests
 */
class AjaxModule extends leieModule
{
    /**
     * @reimp
     */
    public function process()
    {
        $module = $this->getParam( 'Module' );
        $type = $this->getParam( 'Type', false, 'json' );

        $class = ucfirst( $module ) . 'Module';
        if ( !class_exists( $class ) )
        {
            throw new leieRunTimeException( "Module '$module' does not exist" );
        }

        $object = new $class();
        $resultModule = $object->process();
        $result = $resultModule->Result;
        $errorList = leieExceptionHandler::getErrorMessageList();

        switch ( $type )
        {
            default:
            case 'json':
            {
                $list = array( 'error_message' => !$result ? $errorList : '',
                               'result' => $result,
                            );

                $data = json_encode( $list );
                echo $data;

                exit;
            } break;

            case 'pagelayout':
            {
                echo $resultModule->handle();
            } break;
        }

        exit;
    }
}
?>