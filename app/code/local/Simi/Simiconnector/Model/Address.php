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

    /*
     * Save Customer Address
     */
    public function saveAddress($data) {
        $data = $data['contents'];        
        $address = Mage::helper('simiconnector/address')->convertDataAddress($data);
        $address['id'] = isset($data->entity_id) == true ? $data->entity_id : null;
        return $this->saveAddressCustomer($address);
        
    }

    public function saveAddressCustomer($data) {
        $errors = false;
        $customer = $this->_getSession()->getCustomer();
        $address = Mage::getModel('customer/address');
        $addressId = $data['id'];
        $address->setData($data);

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
            if (is_array($addressErrors))
                throw new Exception($addressErrors[0],7);
            throw new Exception($this->_helperAddress()->__('Can not save address customer'),7);
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
