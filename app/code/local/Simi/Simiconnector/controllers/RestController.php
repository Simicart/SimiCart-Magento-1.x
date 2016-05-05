<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/2/16
 * Time: 4:17 PM
 */
class Simi_Simiconnector_RestController extends Simi_Simiconnector_Controller_Action
{
    public function v2Action(){
        ob_start();
        try{
            $result = $this->_getServer()
                ->init($this)->run();
            $this->_printData($result);
        }catch (Exception $e){
            $result = array();
            $result['error'] = array(
                'code' => $e->getCode(),
                'message'=> $e->getMessage(),
            );
            $this->_printData($result);
        }
        exit();
        ob_end_flush();
    }

}