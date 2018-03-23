<?php

class Simi_Simiconnector_Helper_Productlabel extends Mage_Core_Helper_Abstract
{

    static public function getOptionArray() 
    {
        return array(
            1 => Mage::helper('simiconnector')->__('Top-left'),
            2 => Mage::helper('simiconnector')->__('Top-center'),
            3 => Mage::helper('simiconnector')->__('Top-right'),
            4 => Mage::helper('simiconnector')->__('Middle-left'),
            5 => Mage::helper('simiconnector')->__('Middle-center'),
            6 => Mage::helper('simiconnector')->__('Middle-right'),
            7 => Mage::helper('simiconnector')->__('Bottom-left'),
            8 => Mage::helper('simiconnector')->__('Bottom-center'),
            9 => Mage::helper('simiconnector')->__('Bottom-right'),
        );
    }

    /**
     * get model option hash as array
     *
     * @return array
     */
    static public function getOptionHash() 
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }

        return $options;
    }

    public function getProductLabel($product) {
        if (!Mage::getStoreConfig("simiconnector/productlabel/enable"))
            return;

        $productLabel = Mage::getModel('simiconnector/simiproductlabel')->getCollection()
            ->addFieldToFilter('status', Simi_Simiconnector_Model_Status::STATUS_ENABLED)
            ->addFieldToFilter('storeview_id', Mage::app()->getStore()->getId())
            ->addFieldToFilter('product_ids', array('finset' => $product->getId()))
            ->setOrder('priority','DESC')->getFirstItem();
        if($productLabel->getId()){
            return array(
                'name'=> $productLabel->getData('name'),
                'label_id'=> $productLabel->getData('label_id'),
                'description'=> $productLabel->getData('description'),
                'text'=> $productLabel->getData('text'),
                'image'=> str_replace("http://", "https://", $productLabel->getData('image')),
                'position'=> $productLabel->getData('position'),
            );
        }
        return array();
    }

    public function getProductLabels($product) 
    {
        $labels = array();
        $collection = Mage::getModel('simiconnector/simiproductlabel')->getCollection()->setOrder('priority', 'DESC');
        if($websiteId = Mage::helper('simiconnector/cloud')->getWebsiteIdSimiUser()){
            $storeIds = Mage::app()->getWebsite($websiteId)->getStoreIds();
            $collection = Mage::getModel('simiconnector/simiproductlabel')->getCollection()
                ->addFieldToFilter('storeview_id', array('in'=>$storeIds))
                ->setOrder('priority', 'DESC');
        }

        foreach ($collection as $productLabel) {
            if($productLabel->getData('status') == Simi_Simiconnector_Model_Status::STATUS_DISABLED)
                continue;
            if($productLabel->getData('storeview_id') != Mage::app()->getStore()->getId())
                continue;

            foreach (explode(',', str_replace(' ', '', $productLabel->getData('product_ids'))) as $productId) {
                if ($product->getId() == $productId) {
                    $labels[] = array(
                        'name'=> $productLabel->getData('name'),
                        'label_id'=> $productLabel->getData('label_id'),
                        'description'=> $productLabel->getData('description'),
                        'text'=> $productLabel->getData('text'),
                        'image'=> $productLabel->getData('image'),
                        'position'=> $productLabel->getData('position'),
                    );
                    break;
                }
            }
        }

        return $labels;
    }
}
