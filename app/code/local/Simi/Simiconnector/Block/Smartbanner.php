<?php

class Simi_Simiconnector_Block_Smartbanner extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function isEnabled(){
   		return Mage::getStoreConfig('simiconnector/smartbanner/enable', Mage::app()->getStore()->getId());
    }

    public function getTitle(){
   		return Mage::getStoreConfig('simiconnector/smartbanner/title', Mage::app()->getStore()->getId());
    }

    public function getAuthor(){
   		return Mage::getStoreConfig('simiconnector/smartbanner/author_title', Mage::app()->getStore()->getId());
    }

    public function getAndroidAppId(){
    	return Mage::getStoreConfig('simiconnector/smartbanner/android_app_id', Mage::app()->getStore()->getId());
    }

    public function getIOSAppId(){
    	return Mage::getStoreConfig('simiconnector/smartbanner/ios_app_id', Mage::app()->getStore()->getId());
    }

    public function getAndroidAppIcon(){
    	return Mage::getStoreConfig('simiconnector/smartbanner/android_app_icon', Mage::app()->getStore()->getId());
    }

    public function getIOSAppIcon(){
    	return Mage::getStoreConfig('simiconnector/smartbanner/ios_app_icon', Mage::app()->getStore()->getId());
    }
}