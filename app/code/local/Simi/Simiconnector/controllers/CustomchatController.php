<?php

class Simi_Simiconnector_CustomchatController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $block = Mage::getBlockSingleton('simiconnector/customchat');
        $html = $block->toHtml();
        return $this->getResponse()->setBody($html);
    }
}
