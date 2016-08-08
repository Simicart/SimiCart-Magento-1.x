<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/2/16
 * Time: 4:17 PM
 */
class Simi_Simiconnector_RestController extends Simi_Simiconnector_Controller_Action
{
    public function v2Action()
    {
        ob_start();
        try {
            $result = $this->_getServer()
                ->init($this)->run();
            $this->_printData($result);
        } catch (Exception $e) {
            $results = array();
            $result = array();
            if (is_array($e->getMessage())) {
                $messages = $e->getMessage();
                foreach ($messages as $message) {
                    $result[] = array(
                        'code' => $e->getCode(),
                        'message' => $message,
                    );
                }
            } else {
                $result[] = array(
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                );
            }
            $results['errors'] = $result;
            $this->_printData($results);
        }
        exit();
        ob_end_flush();
    }

}