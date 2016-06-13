<?php

/**

 */
class Simi_Simiconnector_Helper_Address extends Mage_Core_Helper_Abstract {

    public function _getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function convertDataAddress($data) {
        $country = $data->country_id;
        $listState = Mage::helper('simiconnector/address')->getStates($country);
        $state_id = null;

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
            throw new Exception($this->_helperAddress()->__('State invalid'), 4);
        }
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

    public function getAddressDetail($data, $customer) {
        $street = $data->getStreet();
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
            'email' => $customer->getEmail(),
            'company' => $data->getCompany(),
            'latlng' => $street[2] != NULL ? $street[2] : "",
        );
    }

    public function saveBillingAddress($billingAddress) {
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
                throw new Exception($this->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.'));
            }
        }
        $address = $this->convertDataAddress($billingAddress);
        $address['save_in_address_book'] = '1';
        $this->_getOnepage()->saveBilling($address, $billing_address->entity_id);
    }

    public function saveShippingAddress($shippingAddress) {
        $address = $this->convertDataAddress($shippingAddress);
        $address['save_in_address_book'] = '1';
        $this->_getOnepage()->saveShipping($address, $shippingAddress->entity_id);
    }

}
