<?php

class Simi_Simiconnector_Model_Simicategory extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/simicategory');
    }

    public function getCategories() {
        $data = array();
        try {
            $collection = $this->getCollection();
            foreach ($collection as $item) {
                if ($item->getStatus() == 1) {
                    $info = array(
                        'category_id' => $item->getCategoryId(),
                        'category_image' => $item->getSimicategoryFilename(),
                        'category_name' => $item->getSimicategoryName(),
                    );
                    $data[] = $info;
                }
            }

            $information = $this->statusSuccess();
            $information['data'] = $data;
            return $information;
        } catch (Expetion $e) {
            $message = $e->getMessage();
            $information = $this->statusError();
            $information['message'] = array($message);
            if (is_array($message)) {
                $information['message'] = $message;
            }
            return $information;
        }
    }
    public function delete() {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('homecategory');
        $visibleStoreViews = Mage::getModel('simiconnector/visibility')->getCollection()
                ->addFieldToFilter('content_type', $typeID)
                ->addFieldToFilter('item_id', $this->getId());
        foreach ($visibleStoreViews as $visibilityItem)
            $visibilityItem->delete();
        return parent::delete();
    }

}
