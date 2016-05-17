<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Storeviews extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'store_id';

    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            Mage::app()->getCookie()->set(Mage_Core_Model_Store::COOKIE_NAME, Mage::app()->getStore($data['resourceid'])->getCode(), TRUE);
            Mage::app()->setCurrentStore(
                    Mage::app()->getStore($data['resourceid'])->getCode()
            );
            Mage::getSingleton('core/locale')->emulate($data['resourceid']);
            $this->builderQuery = Mage::getModel('core/store')->load($data['resourceid']);
        } else {
            $this->builderQuery = $collection = Mage::getModel('core/store')->getCollection();
        }
    }

    public function show() {
        $information = parent::show();
        $country_code = Mage::getStoreConfig('general/country/default');
        $country = Mage::getModel('directory/country')->loadByCode($country_code);
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $currencySymbol = Mage::app()->getLocale()->currency($currencyCode)->getSymbol();
        $options = Mage::getResourceSingleton('customer/customer')->getAttribute('gender')->getSource()->getAllOptions();
        $values = array();
        foreach ($options as $option) {
            if ($option['value']) {
                $values[] = array(
                    'label' => $option['label'],
                    'value' => $option['value'],
                );
            }
        }

        $rtlCountry = Mage::getStoreConfig('simiconnector/general/rtl_country', Mage::app()->getStore()->getId());
        $isRtl = '0';
        $rtlCountry = explode(',', $rtlCountry);
        if (in_array($country_code, $rtlCountry)) {
            $isRtl = '1';
        }

        $additionInfo = array(
            'base'=> array(),
            'store_config' => array(
                'country_code' => $country->getId(),
                'country_name' => $country->getName(),
                'locale_identifier' => $locale,
                'currency_symbol' => $currencySymbol,
                'currency_code' => $currencyCode,
                'currency_position' => $this->getCurrencyPosition(),
                'store_id' => $this->getCurrentStoreId(),
                'store_name' => Mage::app()->getStore()->getName(),
                'store_code' => Mage::app()->getStore()->getCode(),
                'is_show_zero_price' => Mage::getStoreConfig('simiconnector/general/is_show_price_zero'),
                'is_show_link_all_product' => Mage::getStoreConfig('simiconnector/general/is_show_all_product'),
                'use_store' => Mage::getStoreConfig('web/url/use_store'),
                'is_reload_payment_method' => Mage::getStoreConfig('simiconnector/general/is_reload_payment_method'),
                'is_rtl' => $isRtl,
            ),
            'customer_address_config' => array(
                'prefix_show' => Mage::getStoreConfig('customer/address/prefix_show'),
                'suffix_show' => Mage::getStoreConfig('customer/address/suffix_show'),
                'dob_show' => Mage::getStoreConfig('customer/address/dob_show'),
                'taxvat_show' => Mage::getStoreConfig('customer/address/taxvat_show'),
                'gender_show' => Mage::getStoreConfig('customer/address/gender_show'),
                'gender_value' => $values,
            ),
            'checkout_config' => array(
                'enable_guest_checkout' => Mage::getStoreConfig('checkout/options/guest_checkout'),
                'enable_agreements' => is_null(Mage::getStoreConfig('checkout/options/enable_agreements')) ? 0 : Mage::getStoreConfig('checkout/options/enable_agreements'),
                'taxvat_show' => Mage::getStoreConfig('customer/create_account/vat_frontend_visibility'),
            ),
            'view_products_default' => Mage::getStoreConfig('simiconnector/general/show_product_type'),
            'android_sender' => Mage::getStoreConfig('simiconnector/android_sendid'),
        );

        $information['storeview'] = array_merge($information['storeview'], $additionInfo);
        return $information;
    }

    public function getCurrencyPosition() {
        $formated = Mage::app()->getStore()->getCurrentCurrency()->formatTxt(0);
        $number = Mage::app()->getStore()->getCurrentCurrency()->formatTxt(0, array('display' => Zend_Currency::NO_SYMBOL));
        // Zend_debug::dump($number);
        $ar_curreny = explode($number, $formated);
        if ($ar_curreny['0'] != '') {
            return 'before';
        }
        return 'after';
    }

    public function getCurrentStoreId() {
        return Mage::app()->getStore()->getId();
    }

}
