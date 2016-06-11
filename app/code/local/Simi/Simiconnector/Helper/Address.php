<?php

/**

 */
class Simi_Simiconnector_Helper_Address extends Mage_Core_Helper_Abstract {

    public function convertDataAddress($data, $region_id) {
        $latlng = isset($data->latlng) == true ? $data->latlng : '';
        $address = array();
        foreach ((array) $data as $index => $info) {
            $address[$index] = $info;
        }
        $address['street'] = array($data->street, '', $latlng, '');
        $address['region_id'] = $region_id;
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
            'country_name' => $data->getCountryModel()->loadByCode($data->getCountry())->getName(),
            'country_id' => $data->getCountry(),
            'telephone' => $data->getTelephone(),
            'email' => $customer->getEmail(),
            'company' => $data->getCompany(),
            'latlng' => $street[2] != NULL ? $street[2] : "",
        );
    }

}
