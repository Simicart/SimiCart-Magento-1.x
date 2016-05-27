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

}
