<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Storeviews extends Simi_Simiconnector_Model_Api_Abstract {

    public $storeviewInfo;
    protected $_DEFAULT_ORDER = 'store_id';
    protected $_method = 'callApi';
    protected $group_id;

    public function setBuilderQuery() {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->setStoreView($data);
            $this->setCurrency($data);
            $this->builderQuery = Mage::getModel('core/store')->load($data['resourceid']);
        } else {
            $this->builderQuery = Mage::getModel('core/store')->getCollection()->addFieldToFilter('group_id', Mage::app()->getStore()->getGroupId());
        }
    }

    public function index() {
        $result = parent::index();
        foreach ($result['storeviews'] as $index => $storeView) {
            $result['storeviews'][$index]['base_url'] = Mage::getStoreConfig('simiconnector/general/base_url', $storeView['store_id']);
        }
        return $result;
    }

    public function show() {
        //$information = parent::show();
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

        $currencies = $this->getCurrencies();

        $cmsData = $this->getData();
        $cmsData['resourceid'] = NULL;
        $cmsData['resource'] = 'cmspages';
        $model = Mage::getSingleton('simiconnector/api_cmspages');
        $cmsPageList = call_user_func_array(array(&$model, $this->_method), array($cmsData));
        $additionInfo = array(
            'base' => array(
                'country_code' => $country->getId(),
                'country_name' => $country->getName(),
                'locale_identifier' => $locale,
                'store_id' => $this->getCurrentStoreId(),
                'store_name' => Mage::app()->getStore()->getName(),
                'store_code' => Mage::app()->getStore()->getCode(),
                'group_id' => Mage::app()->getStore()->getGroupId(),
                'base_url' => Mage::getStoreConfig('simiconnector/general/base_url'),
                'use_store' => Mage::getStoreConfig('web/url/use_store'),
                'is_rtl' => Mage::getStoreConfig('simiconnector/general/is_rtl'),
                'is_show_sample_data' => Mage::getStoreConfig('simiconnector/general/is_show_sample_data'),
                'android_sender' => Mage::getStoreConfig('simiconnector/notification/android_app_key'),
                'currency_symbol' => $currencySymbol,
                'currency_code' => $currencyCode,
                'currency_position' => $this->getCurrencyPosition(),
                'thousand_separator' => Mage::getStoreConfig('simiconnector/currency/thousand_separator'),
                'decimal_separator' => Mage::getStoreConfig('simiconnector/currency/decimal_separator'),
                'min_number_of_decimals' => Mage::getStoreConfig('simiconnector/currency/min_number_of_decimals'),
                'max_number_of_decimals' => Mage::getStoreConfig('simiconnector/currency/max_number_of_decimals'),
                'currencies' => $currencies,
                'is_show_home_title' => Mage::getStoreConfig('simiconnector/general/is_show_home_title'),
                'cust_group' => Mage::getSingleton('customer/session')->getCustomerGroupId(),
                'customer_identity' => Mage::getSingleton('core/session')->getEncryptedSessionId(),
                'customer_ip'=>$_SERVER["REMOTE_ADDR"],
            ),
            'sales' => array(
                'sales_reorder_allow' => Mage::getStoreConfig('sales/reorder/allow'),
                'sales_totals_sort_subtotal' => Mage::getStoreConfig('sales/totals_sort/subtotal'),
                'sales_totals_sort_discount' => Mage::getStoreConfig('sales/totals_sort/discount'),
                'sales_totals_sort_shipping' => Mage::getStoreConfig('sales/totals_sort/shipping'),
                'sales_totals_sort_weee' => Mage::getStoreConfig('sales/totals_sort/weee'),
                'sales_totals_sort_tax' => Mage::getStoreConfig('sales/totals_sort/tax'),
                'sales_totals_sort_grand_total' => Mage::getStoreConfig('sales/totals_sort/grand_total'),
            ),
            'checkout' => array(
                'enable_guest_checkout' => Mage::getStoreConfig('checkout/options/guest_checkout'),
                'enable_agreements' => is_null(Mage::getStoreConfig('checkout/options/enable_agreements')) ? 0 : Mage::getStoreConfig('checkout/options/enable_agreements'),
            ),
            'tax' => array(
                'tax_display_type' => Mage::getStoreConfig('tax/display/type'),
                'tax_display_shipping' => Mage::getStoreConfig('tax/display/shipping'),
                'tax_cart_display_price' => Mage::getStoreConfig('tax/cart_display/price'),
                'tax_cart_display_subtotal' => Mage::getStoreConfig('tax/cart_display/subtotal'),
                'tax_cart_display_shipping' => Mage::getStoreConfig('tax/cart_display/shipping'),
                'tax_cart_display_grandtotal' => Mage::getStoreConfig('tax/cart_display/grandtotal'),
                'tax_cart_display_full_summary' => Mage::getStoreConfig('tax/cart_display/full_summary'),
                'tax_cart_display_zero_tax' => Mage::getStoreConfig('tax/cart_display/zero_tax'),
                'tax_sales_display_price' => Mage::getStoreConfig('tax/sales_display/price'),
                'tax_sales_display_subtotal' => Mage::getStoreConfig('tax/sales_display/subtotal'),
                'tax_sales_display_shipping' => Mage::getStoreConfig('tax/sales_display/shipping'),
                'tax_sales_display_grandtotal' => Mage::getStoreConfig('tax/sales_display/grandtotal'),
                'tax_sales_display_full_summary' => Mage::getStoreConfig('tax/sales_display/full_summary'),
                'tax_sales_display_zero_tax' => Mage::getStoreConfig('tax/sales_display/zero_tax'),
            ),
            'google_analytics' => array(
                'google_analytics_active' => Mage::getStoreConfig('google/analytics/active'),
                'google_analytics_type' => Mage::getStoreConfig('google/analytics/type'),
                'google_analytics_account' => Mage::getStoreConfig('google/analytics/account'),
                'google_analytics_anonymization' => Mage::getStoreConfig('google/analytics/anonymization'),
            ),
            'customer' => array(
                'address_option' => array(
                    'prefix_show' => Mage::getStoreConfig('customer/address/prefix_show'),
                    'middlename_show' => Mage::getStoreConfig('customer/address/middlename_show'),
                    'suffix_show' => Mage::getStoreConfig('customer/address/suffix_show'),
                    'dob_show' => Mage::getStoreConfig('customer/address/dob_show'),
                    'taxvat_show' => Mage::getStoreConfig('customer/address/taxvat_show'),
                    'gender_show' => Mage::getStoreConfig('customer/address/gender_show'),
                    'gender_value' => $values,
                    'region_state_required' => Mage::getStoreConfig('general/region/state_required'),
                    'region_display_all' => Mage::getStoreConfig('general/region/display_all'),
                ),
                'account_option' => array(
                    'taxvat_show' => Mage::getStoreConfig('customer/create_account/vat_frontend_visibility'),
                ),
            ),
            'wishlist' => array(
                'wishlist_general_active' => Mage::getStoreConfig('wishlist/general/active'),
                'wishlist_wishlist_link_use_qty' => Mage::getStoreConfig('wishlist/wishlist_link/use_qty'),
            ),
            'catalog' => array(
                'frontend' => array(
                    'view_products_default' => Mage::getStoreConfig('simiconnector/general/show_product_type'),
                    'is_show_zero_price' => Mage::getStoreConfig('simiconnector/general/is_show_price_zero'),
                    'is_show_link_all_product' => Mage::getStoreConfig('simiconnector/general/is_show_all_product'),
                    'catalog_frontend_list_mode' => Mage::getStoreConfig('catalog/frontend/list_mode'),
                    'catalog_frontend_grid_per_page_values' => Mage::getStoreConfig('catalog/frontend/grid_per_page_values'),
                    'catalog_frontend_list_per_page' => Mage::getStoreConfig('catalog/frontend/list_per_page'),
                    'catalog_frontend_list_allow_all' => Mage::getStoreConfig('catalog/frontend/list_allow_all'),
                    'catalog_frontend_default_sort_by' => Mage::getStoreConfig('catalog/frontend/default_sort_by'),
                    'catalog_frontend_flat_catalog_category' => Mage::getStoreConfig('catalog/frontend/flat_catalog_category'),
                    'catalog_frontend_flat_catalog_product' => Mage::getStoreConfig('catalog/frontend/flat_catalog_product'),
                    'catalog_frontend_parse_url_directives' => Mage::getStoreConfig('catalog/frontend/parse_url_directives'),
                ),
                'review' => array(
                    'catalog_review_allow_guest' => Mage::getStoreConfig('catalog/review/allow_guest'),
                ),
                'cataloginventory' => array(
                    'cataloginventory_item_options_manage_stock' => Mage::getStoreConfig('cataloginventory/item_options/manage_stock'),
                    'cataloginventory_item_options_backorders' => Mage::getStoreConfig('cataloginventory/item_options/backorders'),
                    'cataloginventory_item_options_max_sale_qty' => Mage::getStoreConfig('cataloginventory/item_options/max_sale_qty'),
                    'cataloginventory_item_options_min_qty' => Mage::getStoreConfig('cataloginventory/item_options/options_min_qty'),
                    'cataloginventory_item_options_min_sale_qty' => Mage::getStoreConfig('cataloginventory/item_options/min_sale_qty'),
                    'cataloginventory_item_options_notify_stock_qty' => Mage::getStoreConfig('cataloginventory/item_options/notify_stock_qty'),
                    'cataloginventory_item_options_enable_qty_increments' => Mage::getStoreConfig('cataloginventory/item_options/enable_qty_increments'),
                    'cataloginventory_item_options_qty_increments' => Mage::getStoreConfig('cataloginventory/item_options/qty_increments'),
                    'cataloginventory_item_options_auto_return' => Mage::getStoreConfig('cataloginventory/item_options/auto_return'),
                )
            ),
            'cms' => $cmsPageList,
            'category_cmspages' => Mage::getModel('simiconnector/cms')->getCategoryCMSPages(),
            'zopim_config' => array(
                'enable' => Mage::getStoreConfig('simiconnector/zopim/enable'),
                'account_key' => Mage::getStoreConfig('simiconnector/zopim/account_key'),
                'show_profile' => Mage::getStoreConfig('simiconnector/zopim/show_profile'),
                'name' => Mage::getStoreConfig('simiconnector/zopim/name'),
                'email' => Mage::getStoreConfig('simiconnector/zopim/email'),
                'phone' => Mage::getStoreConfig('simiconnector/zopim/phone'),
            ),
            'mixpanel_config' => array(
                'token' => Mage::getStoreConfig('simiconnector/mixpanel/token'),
            ),
            'allowed_countries' => $this->getAllowedCountries(),
            'stores' => $this->getStores(),
        );
        if ($checkout_info_setting = Mage::helper('simiconnector/address')->getCheckoutAddressSetting())
            $additionInfo['customer']['address_fields_config'] = $checkout_info_setting;

        if ($checkout_terms = Mage::helper('simiconnector/checkout')->getCheckoutTermsAndConditions())
            $additionInfo['checkout']['checkout_terms_and_conditions'] = $checkout_terms;
        //Scott add to get instant contacts
        if (Mage::helper('simiconnector/plugins_instantcontact')->isEnabled())
            $additionInfo['instant_contact'] = Mage::helper('simiconnector/plugins_instantcontact')->getContacts();

        $this->storeviewInfo = $additionInfo;
        Mage::dispatchEvent('simiconnector_get_storeview_info_after', array('object' => $this));
        return $this->getDetail($this->storeviewInfo);
    }

    public function getAllowedCountries() {
        $list = array();
        $country_default = Mage::getStoreConfig('general/country/default');
        $countries = Mage::getResourceModel('directory/country_collection')->loadByStore();
        $cache = null;
        foreach ($countries as $country) {
            if ($country_default == $country->getId()) {
                $cache = array(
                    'country_code' => $country->getId(),
                    'country_name' => $country->getName(),
                    'states' => Mage::helper('simiconnector/address')->getStates($country->getId()),
                );
            } else {
                $list[] = array(
                    'country_code' => $country->getId(),
                    'country_name' => $country->getName(),
                    'states' => Mage::helper('simiconnector/address')->getStates($country->getId()),
                );
            }
        }
        if ($cache) {
            array_unshift($list, $cache);
        }
        return $list;
    }

    public function getCurrencyPosition() {
        $formated = Mage::app()->getStore()->getCurrentCurrency()->formatTxt(0);
        $number = Mage::app()->getStore()->getCurrentCurrency()->formatTxt(0, array('display' => Zend_Currency::NO_SYMBOL));
        $ar_curreny = explode($number, $formated);
        if ($ar_curreny['0'] != '') {
            return 'before';
        }
        return 'after';
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
                    if (!$title = Mage::app()->getLocale()->getTranslation($code, 'nametocurrency'))
                        $title = $code;
                    $currencies[] = array(
                        'value' => $code,
                        'title' => $title,
                    );
                }
            }
        } elseif (count($codes) == 1) {
            $currencies[] = array(
                'value' => $codes[0],
                'title' => Mage::app()->getLocale()->getTranslation($codes[0], 'nametocurrency'),
            );
        }
        return $currencies;
    }

    public function setCurrency($data) {
        if (isset($data['params']['currency'])) {
            $currency = $data['params']['currency'];
            if ($currency) {
                Mage::app()->getStore()->setCurrentCurrencyCode($currency);
                Mage::app()->getCookie()->set('currency_code', $currency, TRUE);
            }
        }
    }

    public function setStoreView($data) {
        if (($data['resourceid'] == 'default') || ($data['resourceid'] == Mage::app()->getStore()))
            return;
        Mage::app()->getCookie()->set(Mage_Core_Model_Store::COOKIE_NAME, Mage::app()->getStore($data['resourceid'])->getCode(), TRUE);
        Mage::app()->setCurrentStore(
                Mage::app()->getStore($data['resourceid'])->getCode()
        );
        Mage::getSingleton('core/locale')->emulate($data['resourceid']);
    }

    public function getCurrentStoreId() {
        return Mage::app()->getStore()->getId();
    }

    public function getStores() {
        $storeAPIModel = Mage::getModel('simiconnector/api_stores');
        $storeAPIModel->setData($this->getData());
        $storeAPIModel->builderQuery = Mage::getModel('core/store_group')->getCollection()->addFieldToFilter('website_id', Mage::app()->getStore()->getWebsiteId());
        $storeAPIModel->pluralKey = 'stores';
        return $storeAPIModel->index();
    }

}
