<?php

/**
 * 
 */
class Simi_Simiconnector_Model_Api_Addresses extends Simi_Simiconnector_Model_Api_Abstract
{

    protected $_DEFAULT_ORDER = 'entity_id';

    public function setSingularKey($singularKey) 
    {
        $this->singularKey = 'Address';
        return $this;
    }

    public function setBuilderQuery() 
    {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->builderQuery = Mage::getModel('customer/address')->load($data['resourceid']);
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

    public function store() 
    {
        $data = $this->getData();
        $address = Mage::getModel('simiconnector/address')->saveAddress($data);
        $this->builderQuery = $address;
        return $this->show();
    }

    /*
     * Edit Address
     */

    public function update() 
    {
        $data = $this->getData();
        $address = Mage::getModel('simiconnector/address')->saveAddress($data);
        $this->builderQuery = $address;
        return $this->show();
    }
    
    /*
     * Remove Address
     */

    public function destroy() 
    {
        $data = $this->getData();
        if ($data['resourceid']) {
           $this->builderQuery = Mage::getModel('customer/address')->load($data['resourceid']);
           $this->builderQuery->delete();
           return $this->show();
        }

        throw new Exception($this->_helper->__('No Address ID sent'), 4);
    }
    
    
    /*
     * View Address Detail
     */

    public function index() 
    {
        $result = parent::index();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $addresses = $result['addresses'];
        foreach ($addresses as $index => $address) {
            $addressModel = Mage::getModel('customer/address')->load($address['entity_id']);
            $addresses[$index] = array_merge($address, Mage::helper('simiconnector/address')->getAddressDetail($addressModel, $customer));
        }

        $result['addresses'] = $addresses;
        return $result;
    }

    /*
     * Geocoding
     */

    public function show() 
    {
        $data = $this->getData();
        if ($data['resourceid']) {
            if ($data['resourceid'] == 'geocoding') {
                $result = array();
                $addressDetail = array();
                $longitude = $data['params']['longitude'];
                $latitude = $data['params']['latitude'];
                $dataresult = Mage::helper('simiconnector/address')->getLocationInfo($latitude, $longitude);
                $addressDetail = $dataresult;
               /* $dataresult = $dataresult['geocoding'];
                for ($j = 0; $j < count($dataresult->results[0]->address_components); $j++) {
                    $addressComponents = $dataresult->results[0]->address_components[$j];
                    $types = $addressComponents->types;
                    $address = '';
                    if (in_array('street_number', $types)) {
                        $address .= $addressComponents->long_name;
                    }

                    if (in_array('route', $types)) {
                        $address .= ' ' . $addressComponents->long_name;
                    }

                    if (in_array('locality', $types)) {
                        $address .= ', ' . $addressComponents->long_name;
                    }

                    $addressDetail['street'] = $address;
                    if (in_array('postal_town', $types) || in_array('administrative_area_level_1', $types)) {
                        $addressDetail['region'] = $addressComponents->long_name;
                        $addressDetail['region_id'] = $addressComponents->short_name;
                    }

                    if (in_array('administrative_area_level_2', $types)) {
                        $addressDetail['city'] = $addressComponents->short_name;
                    }

                    if (in_array('country', $types)) {
                        $addressDetail['country_name'] = $addressComponents->long_name;
                        $addressDetail['country_id'] = $addressComponents->short_name;
                    }

                    if (in_array('postal_code', $types)) {
                        $addressDetail['postcode'] = $addressComponents->long_name;
                    }
                }*/

                $addressDetail['region']    = $addressDetail['state'];
                $addressDetail['region_id'] = $addressDetail['state'];
                $addressDetail['country_name'] = $addressDetail['country'];
                $addressDetail['country_id'] = $addressDetail['country'];
                $addressDetail['postcode'] = $addressDetail['zipcode'];
                $addressDetail['street'] = $addressDetail['address'];
                $result['address'] = $addressDetail;
                return $result;
            }
        }

        $result = parent::show();
        if($result && is_array($result) && isset($result['Address'])) {
            $result['Address'] = array_merge($result['Address'], Mage::helper('simiconnector/address')->getAddressDetail($this->builderQuery));
        }

        return $result;
    }

}
