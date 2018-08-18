<?php
/**
 * Created by PhpStorm.
 * User: scott
 * Date: 8/18/18
 * Time: 5:49 PM
 */

class Simi_Simiconnector_Block_Customchat extends Mage_Core_Block_Template
{

    public function __construct()
    {
        $this->setTemplate('simiconnector/custom_chat/script.phtml');
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getHeadScript(){
        return Mage::getStoreConfig('simiconnector/customchat/head_script');
    }

    public function getBodyScript()
    {
        return Mage::getStoreConfig('simiconnector/customchat/body_script');
    }

    public function isEnabledChat()
    {
        return Mage::getStoreConfig('simiconnector/customchat/enable',Mage::app()->getStore()->getId());
    }
}