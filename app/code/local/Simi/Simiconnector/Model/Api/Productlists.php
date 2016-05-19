<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Productlists extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';

    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/productlist')->load($data['resourceid']);
        } else {
            $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('productlist');
            $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
            $listCollection = Mage::getModel('simiconnector/productlist')->getCollection();
            $listCollection->getSelect()
                    ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.productlist_id AND visibility.content_type = '.$typeID.' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());
            $this->builderQuery = $listCollection;
        }
    }
}
