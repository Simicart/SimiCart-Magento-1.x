<?php

/**
 * Created by PhpStorm.
 * User: scottsimicart
 * Date: 9/12/17
 * Time: 9:11 AM
 */
class Simi_Simiconnector_Block_Adminhtml_Siminotification_Edit_Tab_Renderer_Storeviews
    extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{

    public function __construct()
    {
        $this->setModel('android');
        $this->setTemplate('simiconnector/siminotification/renderer/storeview.phtml');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function getGroups(){
        if($websiteId = Mage::helper('simiconnector/cloud')->getWebsiteIdSimiUser()){
            return Mage::getModel('core/store_group')->getCollection()->addFieldToFilter('website_id', $websiteId);
        }
        return Mage::getModel('core/store_group')->getCollection();
    }

    public function getStoreviews(){
        if($websiteId = Mage::helper('simiconnector/cloud')->getWebsiteIdSimiUser()){
            return Mage::getModel('core/store')->getCollection()->addFieldToFilter('website_id', $websiteId);
        }
        return Mage::getModel('core/store')->getCollection();
    }

    public function getCurrentStorviews(){
        if ($data = Mage::registry('siminotification_data')) {
            $data = $data->getData();
            if(is_array($data) && isset($data['storeview_id']))
                return $data['storeview_id'];
        }
        return '';
    }
}