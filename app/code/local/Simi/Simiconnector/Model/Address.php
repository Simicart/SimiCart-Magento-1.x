<?php

/**
 * 
 */
class Simi_Simiconnector_Model_Address extends Mage_Core_Model_Abstract {

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    protected function _helperAddress() {
        return Mage::helper('simiconnector/address');
    }

    public function getAddressDetail($data, $customer, $id = '') {
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
            'country_name' => $data->getCountryModel()->loadByCode($data->getCountry())->getName(),
            'country_id' => $data->getCountry(),
            'telephone' => $data->getTelephone(),
            'email' => $customer->getEmail(),
            'company' => $data->getCompany(),
            'latlng' => $street[2] != NULL ? $street[2] : "",
        );
    }

    public function saveAddress($data) {
        $data = $data['contents'];
        $country = $data->country_code;
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
        $address = Mage::helper('simiconnector/address')->convertDataAddress($data, $state_id);
        $address['id'] = isset($data->entity_id) == true ? $data->entity_id : null;
        return $this->saveAddressCustomer($address);
        
    }

    public function saveAddressCustomer($data) {
        $errors = false;
        $customer = $this->_getSession()->getCustomer();
        $address = Mage::getModel('customer/address');
        $addressId = $data['id'];
        if (version_compare(Mage::getVersion(), '1.4.2.0', '<') === true) {
            $address->setData($data);
        }
        if ($addressId && $addressId != '') {
            $existsAddress = $customer->getAddressById($addressId);
            if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                $address->setId($existsAddress->getId());
            }
        } else {
            $address->setId(null);
        }

        if (version_compare(Mage::getVersion(), '1.4.2.0', '>=') === true) {
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                    ->setEntity($address);
        }
        if (version_compare(Mage::getVersion(), '1.4.2.0', '>=') === true) {
            $addressForm->compactData($data);
        }
        $address->setCustomerId($customer->getId());
        $addressErrors = $address->validate();
        if ($addressErrors !== true) {
            $errors = true;
        }
        if (!$errors) {
            $address->save();
            return $address;
        } else {
            throw new Exception($this->_helperAddress()->__('Can not save address customer'));
        }
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

}
