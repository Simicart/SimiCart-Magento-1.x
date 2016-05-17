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

    public function getCurrencies() {
        $currencies = array();
        $codes = Mage::app()->getStore()->getAvailableCurrencyCodes(true);
        if (is_array($codes) && count($codes) > 1) {

            $rates = Mage::getModel('directory/currency')->getCurrencyRates(
                    Mage::app()->getStore()->getBaseCurrency(), $codes
            );

            foreach ($codes as $code) {
                if (isset($rates[$code])) {
                    $currencies[] = array(
                        'value' => $code,
                        'title' => Mage::app()->getLocale()->getTranslation($code, 'nametocurrency'),
                    );
                }
            }
        } elseif (count($codes) == 1) {
            # code...
            $currencies[] = array(
                'value' => $codes[0],
                'title' => Mage::app()->getLocale()->getTranslation($codes[0], 'nametocurrency'),
            );
        }
        $information = $this->statusSuccess();
        $information['data'] = $currencies;
        return $information;
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
