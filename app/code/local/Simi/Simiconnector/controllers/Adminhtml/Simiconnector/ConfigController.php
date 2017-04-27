<?php

/**
 * Created by PhpStorm.
 * User: Scott
 * Date: 5/20/2016
 * Time: 11:15 AM
 */
class Simi_Simiconnector_Adminhtml_Simiconnector_ConfigController extends Mage_Adminhtml_Controller_Action {

    protected function _initItem() {
        if (!Mage::registry('simiconnector_categories')) {
            if ($storecode = Mage::app()->getRequest()->getParam('store')) {
                $storeviewModel = Mage::getModel('core/store')->getCollection()->addFieldToFilter('code', $storecode)->getFirstItem();
                Mage::register('simiconnector_categories', Mage::getStoreConfig('simiconnector/general/categories_in_app', $storeviewModel->getId()));
            } else
                Mage::register('simiconnector_categories', Mage::getStoreConfig('simiconnector/general/categories_in_app'));
        }
    }

    public function categoriesAction() {
        $this->_initItem();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('simiconnector/adminhtml_system_config_category_categories')->toHtml()
        );
    }

    public function categoriesJsonAction() {
        $this->_initItem();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('simiconnector/adminhtml_system_config_category_categories')
                        ->getCategoryChildrenJson($this->getRequest()->getParam('category')));
    }

}
