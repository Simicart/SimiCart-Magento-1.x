<?php

/**
 * 
 */
class Simi_Simiconnector_Model_Api_Addresses extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'entity_id';

    public function setSingularKey($singularKey) {
        $this->singularKey = 'Address';
        return $this;
    }

    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            
        } else {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                throw new Exception($this->_helper->__('You have not logged in'), 4);
            } else {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $addressArray = array();
                $billing = $customer->getPrimaryBillingAddress();
                if ($billing) {
                    $addressArray[] = $billing->getId();
                }
                $shipping = $customer->getPrimaryShippingAddress();
                if ($shipping) {
                    $addressArray[] = $shipping->getId();
                }
                foreach ($customer->getAddresses() as $index => $address) {
                    $addressArray[] = $index;
                }
                $this->builderQuery = Mage::getModel('customer/address')->getCollection()
                        ->addFieldToFilter('entity_id', array('in' => $addressArray));
            }
        }
    }

    /*
     * Add Address
     */

    public function store() {
        $data = $this->getData();
        $address = Mage::getModel('simiconnector/address')->saveAddress($data);
        $this->builderQuery = $address;
        return $this->show();
    }

    /*
     * Edit Address
     */

    public function update() {
        $data = $this->getData();
        $address = Mage::getModel('simiconnector/address')->saveAddress($data);
        $this->builderQuery = $address;
        return $this->show();
    }


    /*
     * Add Address Detail
     */
    public function index() {
        $result = parent::index();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $addresses = $result['addresses'];
        foreach ($addresses as $index => $address) {
            $addressModel = Mage::getModel('customer/address')->load($address['entity_id']);
            $addresses[$index] = array_merge($address, Mage::getModel('simiconnector/address')->getAddressDetail($addressModel, $customer, $address['entity_id']));
        }
        $result['addresses'] = $addresses;
        return $result;
    }

}
