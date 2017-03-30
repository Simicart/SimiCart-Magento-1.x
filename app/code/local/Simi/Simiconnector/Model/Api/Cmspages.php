<?php

class Simi_Simiconnector_Model_Api_Cmspages extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';

    public function setBuilderQuery() {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/cms')->load($data['resourceid']);
        } else {
            $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('cms');
            $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
            $cmsCollection = Mage::getModel('simiconnector/cms')->getCollection()->addFieldToFilter('type','1');
            $cmsCollection->getSelect()
                    ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.cms_id AND visibility.content_type = '.$typeID.' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());
            $this->builderQuery = $cmsCollection;
        }
    }

}
