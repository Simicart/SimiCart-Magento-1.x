<?php

/**

 */
class Simi_Simiconnector_Helper_Address extends Mage_Core_Helper_Abstract {

    public function _getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /*
     * Convert Address before Saving
     */

    public function convertDataAddress($data) {
		$listState = array();
		$check_state = false;
		if (isset($data->country_id)) {
			$country = $data->country_id;
			$listState = Mage::helper('simiconnector/address')->getStates($country);
		}
        $state_id = Mage::getStoreConfig('simiconnector/hideaddress/region_id_default');
        if (count($listState) == 0) {
            $check_state = true;
        }

        foreach ($listState as $state) {
            if (in_array($data->region_code, $state) || in_array($data->region, $state) || in_array($data->region_id, $state)) {
                $state_id = $state['state_id'];
                $check_state = true;
                break;
            }
        }
        if (!$check_state) {
            if (!$state_id)
                throw new Exception($this->__('State invalid'), 4);
        }
        if (!isset($data->country_id) && !isset($data->country_name))
            $data->country_id = Mage::getStoreConfig('simiconnector/hideaddress/country_id_default');

        if (!isset($data->street))
            $data->street = Mage::getStoreConfig('simiconnector/hideaddress/street_default');

        if (!isset($data->city))
            $data->city = Mage::getStoreConfig('simiconnector/hideaddress/city_default');

        if (!isset($data->postcode))
            $data->postcode = Mage::getStoreConfig('simiconnector/hideaddress/zipcode_default');

        if (!isset($data->telephone))
            $data->telephone = Mage::getStoreConfig('simiconnector/hideaddress/telephone_default');

        $latlng = isset($data->latlng) == true ? $data->latlng : '';
        $address = array();
        foreach ((array) $data as $index => $info) {
            $address[$index] = $info;
        }
        $address['street'] = array($data->street, '', $latlng, '');
        $address['region_id'] = $state_id;
        return $address;
    }

    public function getStates($code) {
        $list = array();
        if ($code) {
            $states = Mage::getModel('directory/country')->loadByCode($code)->getRegions();
            foreach ($states as $state) {
                $list[] = array(
                    'state_id' => $state->getRegionId(),
                    'state_name' => $state->getName(),
                    'state_code' => $state->getCode(),
                );
            }
        }
        return $list;
    }

    /*
     * Get Address to be Shown
     */

    public function getAddressDetail($data, $customer = null) {
        if(!$data) return array();
        $street = $data->getStreet();
        if (!($email = $data->getData('email')) && $customer && $customer->getEmail())
            $email = $customer->getEmail();
        return array(
            'firstname' => $data->getFirstname(),
            'lastname' => $data->getLastname(),
            'prefix' => $data->getPrefix(),
            'suffix' => $data->getSuffix(),
            'vat_id' => $data->getVatId(),
            'street' => $street[0],
            'city' => $data->getCity(),
            'region' => $data->getRegion(),
            'region_id' => $data->getRegionId(),
            'region_code' => $data->getRegionCode(),
            'postcode' => $data->getPostcode(),
            'country_name' => $data->getCountry() ? $data->getCountryModel()->loadByCode($data->getCountry())->getName() : NULL,
            'country_id' => $data->getCountry(),
            'telephone' => $data->getTelephone(),
            'email' => $email,
            'company' => $data->getCompany(),
            'fax' => $data->getFax(),
            'latlng' => isset($street[2]) ? $street[2] : "",
        );
    }

    /*
     * Save Billing Address To Quote
     */

    public function saveBillingAddress($billingAddress) {
		$is_register_mode = false;
        if (isset($billingAddress->customer_password) && $billingAddress->customer_password) {
            $is_register_mode = true;
            $this->_getOnepage()->saveCheckoutMethod('register');
        } elseif (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_getOnepage()->saveCheckoutMethod('customer');
        } else {
            $this->_getOnepage()->saveCheckoutMethod('guest');
        }

        if ($is_register_mode) {
            $customer_email = $billingAddress->email;
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->loadByEmail($customer_email);
            if ($customer->getId()) {
                throw new Exception($this->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.'), 7);
            }
        }
        $address = $this->convertDataAddress($billingAddress);
        $address['save_in_address_book'] = '1';
        $saveBilling = $this->_getOnepage()->saveBilling($address, $billingAddress->entity_id);

        if (isset($saveBilling['error'])){
            $error_message = '';
            if (is_array($saveBilling['message'])) {
                foreach ($saveBilling['message'] as $error) {
                    $error_message .=$error . "\n";
                }
            } else {
                $error_message = $saveBilling['message'];
            }

            throw new Exception(Mage::helper('core')->__($error_message));
        }
    }

    /*
     * Save Shipping Address To quote
     */

    public function saveShippingAddress($shippingAddress) {
        $address = $this->convertDataAddress($shippingAddress);
        $address['save_in_address_book'] = '1';
        $saveShipping = $this->_getOnepage()->saveShipping($address, $shippingAddress->entity_id);

        if (isset($saveShipping['error'])){
            $error_message = '';
            if (is_array($saveShipping['message'])) {
                foreach ($saveShipping['message'] as $error) {
                    $error_message .=$error . "\n";
                }
            } else {
                $error_message = $saveShipping['message'];
            }

            throw new Exception(Mage::helper('core')->__($error_message));
        }
    }

    /*
     * Add Hidden Address Fields on Storeview Config Result
     */

    public function getCheckoutAddressSetting() {
        if (!Mage::getStoreConfig('simiconnector/hideaddress/hideaddress_enable'))
            return NULL;
        $addresss = array('company', 'street', 'country_id', 'region_id', 'city', 'zipcode',
            'telephone', 'fax', 'prefix', 'suffix', 'dob', 'gender', 'taxvat');
        foreach ($addresss as $address) {
            $path = "simiconnector/hideaddress/" . $address;
            $value = Mage::getStoreConfig($path);
            if (!$value || $value == null || !isset($value))
                $value = 3;
            $address.='_show';
            if ($value == 1)
                $data[$address] = "req";
            else if ($value == 2)
                $data[$address] = "opt";
            else if ($value == 3)
                $data[$address] = "";
        }
        /*
          //sample add custom address fields
          $data['custom_fields'] = array();
          //text field
          $data['custom_fields'][] = array('code' => 'text_field_sample',
          'title' => 'Text Field',
          'type' => 'text',
          'required' => 'opt',
          'position' => '7',
          );
          //number field
          $data['custom_fields'][] = array('code' => 'number_field_sample',
          'title' => 'Number Field',
          'type' => 'number',
          'required' => 'req',
          'position' => '8',
          );
          //single choice Option
          $data['custom_fields'][] = array('code' => 'single_option_sample',
          'title' => 'Sample Field Single Option',
          'type' => 'single_option',
          'required' => '',
          'option_array' => array('Option Single 1', 'Option Single 2', 'Option Single 3'),
          'position' => '9',
          );
          //multi choice Option
          $data['custom_fields'][] = array('code' => 'multi_option_sample',
          'title' => 'Sample Field Multi Option',
          'type' => 'multi_option',
          'required' => 'opt',
          'option_array' => array('Option Multi 1', 'Option Multi 2', 'Option Multi 3', 'Option Multi 4', 'Option Multi 5'),
          'separated_by' => '%',
          'position' => '10',
          );
         */
        return $data;
    }

    /*
     * Get Geocode result from Lat and Long
     */

    public function getLocationInfo($lat, $lng) {
        $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($lng) . '&sensor=false';
        $json = @file_get_contents($url);
        $data = json_decode($json);
        $status = $data->status;
        if ($status == "OK") {
            $addresses = array();
            $address = '';
            for ($j = 0; $j < count($data->results[0]->address_components); $j++) {
                $addressComponents = $data->results[0]->address_components[$j];
                $types = $addressComponents->types;
                if (in_array('street_number', $types)) {
                    $address .= $addressComponents->long_name;
                }
                if (in_array('route', $types)) {
                    $address .= ' ' . $addressComponents->long_name;
                }
                if (in_array('locality', $types)) {
                    $address .= ', ' . $addressComponents->long_name;
                }
                if (in_array('postal_town', $types) || in_array('administrative_area_level_1', $types)) {
                    $city .= $addressComponents->long_name;
                }
                if (in_array('administrative_area_level_2', $types)) {
                    $state .= $addressComponents->long_name;
                }
                if (in_array('country', $types)) {
                    $country .= $addressComponents->short_name;
                }
                if (in_array('postal_code', $types)) {
                    $zipcode .= $addressComponents->long_name;
                }
            }
            $addresses['address'] = $address;
            $addresses['city'] = $city;
            $addresses['state'] = $state;
            $addresses['country'] = $country;
            $addresses['zipcode'] = $zipcode;
            $addresses['geocoding'] = $data;
            return $addresses;
        } else {
            return false;
        }
    }

}
