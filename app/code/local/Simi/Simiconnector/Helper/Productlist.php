<?php

class Simi_Simiconnector_Helper_Productlist extends Mage_Core_Helper_Abstract {

    public function getListTypeId() {
        return array(
            1 => Mage::helper('simiconnector')->__('Custom Product List'),
            2 => Mage::helper('simiconnector')->__('Best Seller'),
            3 => Mage::helper('simiconnector')->__('Most View'),
            4 => Mage::helper('simiconnector')->__('Newly Updated'),
            5 => Mage::helper('simiconnector')->__('Recently Added')
        );
    }

    public function getTypeOption() {
        return array(
            array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Custom Product List')),
            array('value' => 2, 'label' => Mage::helper('simiconnector')->__('Best Seller')),
            array('value' => 3, 'label' => Mage::helper('simiconnector')->__('Most View')),
            array('value' => 4, 'label' => Mage::helper('simiconnector')->__('Newly Updated')),
            array('value' => 5, 'label' => Mage::helper('simiconnector')->__('Recently Added')),
        );
    }

    public function getProductCollection($listModel) {
        $collection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')
                        ->getProductAttributes())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addUrlRewrite();
        switch ($listModel->getData('list_type')) {
            //Product List
            case 1:
                $collection->addFieldToFilter('entity_id', array('in' => explode(',', $listModel->getData('list_products'))));
                break;
            //Best seller
            case 2:
                $collection = Mage::getResourceModel('reports/product_collection')
                        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                        ->addOrderedQty()->addMinimalPrice()
                        ->addTaxPercents()
                        ->addStoreFilter()
                        ->setOrder('ordered_qty', 'desc');
                break;
            //Most Viewed
            case 3:
                $collection = Mage::getResourceModel('reports/product_collection')
                        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                        ->addViewsCount()
                        ->addMinimalPrice()
                        ->addTaxPercents()
                        ->addStoreFilter();
                break;
            //New Updated
            case 4:
                $collection->setOrder('updated_at', 'desc');
                break;
            //Recently Added
            case 5:
                $collection->setOrder('created_at', 'desc');
                break;
            default:
                break;
        }
        return $collection;
    }

}
