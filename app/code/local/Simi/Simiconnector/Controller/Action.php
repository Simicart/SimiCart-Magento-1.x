<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/2/16
 * Time: 4:14 PM
 */
class Simi_Simiconnector_Controller_Action extends Mage_Core_Controller_Front_Action
{

    protected $_data;

    public function preDispatch()
    {
        parent::preDispatch();
        $enable = (int)Mage::getStoreConfig("simiconnector/general/enable");
//        if (!$enable) {
//            echo 'Connect was disable!';
//            header("HTTP/1.0 503");
//            exit();
//        }
//        if (!$this->isHeader()) {
//            echo 'Connect error!';
//            header("HTTP/1.0 401 Unauthorized");
//            exit();
//        }

    }

    protected function _getServer(){
        return Mage::getSingleton('simiconnector/server');
    }

    protected function _printData($result){
        header("Content-Type: application/json");
        $this->setData($result);
        Mage::dispatchEvent($this->getFullActionName(), array('object' => $this, 'data' => $result));
        $this->_data = $this->getData();
        echo Mage::helper('core')->jsonEncode($this->_data);
    }

    protected function isHeader() {
        if (!function_exists('getallheaders')) {

            function getallheaders() {
                $head = array();
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                        $head[$name] = $value;
                    } else if ($name == "CONTENT_TYPE") {
                        $head["Content-Type"] = $value;
                    } else if ($name == "CONTENT_LENGTH") {
                        $head["Content-Length"] = $value;
                    }
                }
                return $head;
            }

        }

        $head = getallheaders();

        // token is key
        $websiteId = Mage::app()->getWebsite()->getId();
        $keyModel = Mage::getModel('connector/key')->getKey($websiteId);
        $token = "";
        foreach ($head as $k => $h) {
            if ($k == "Authorization" || $k == "TOKEN"
                || $k == "Token") {
                $token = $h;
            }
        }
        if (strcmp($token, 'Bearer '.$keyModel->getKeySecret()) == 0)
            return true;
        else
            return false;
    }

    public function getData() {
        return $this->_data;
    }

    public function setData($data) {
        $this->_data = $data;
    }
}