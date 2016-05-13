<?php

/**

 */
class Simi_Simiconnector_Model_Device extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/device');
    }

    public function setDataDevice($data, $device_id) {
        $website = Mage::app()->getStore()->getWebsiteId();
        $latitude = $data->latitude;
        $longitude = $data->longitude;
        $addresses = $this->getAddress($latitude, $longitude);
        $existed_device = $this->getCollection()->addFieldToFilter('device_token', $data->device_token)->getFirstItem();
        if ($existed_device->getId())
            $this->setId($existed_device->getId());
        if ($addresses) {
            $this->setData('address', $addresses['address']);
            $this->setData('city', $addresses['city']);
            $this->setData('state', $addresses['state']);
            $this->setData('country', $addresses['country']);
            $this->setData('zipcode', $addresses['zipcode']);
        }
        $this->setData('device_token', $data->device_token);
        $this->setData('plaform_id', $device_id);
        $this->setData('website_id', $website);
        $this->setData('latitude', $data->latitude);
        $this->setData('longitude', $data->longitude);
        $this->setData('created_time', now());
        $this->setData('user_email', $data->user_email);
        if (is_null($data->demo_mode)) {
            $this->setData('is_demo', 3);
        } else
            $this->setData('is_demo', $data->demo_mode);
        try {
            $this->save();
            $information = $this->statusSuccess();
            return $information;
        } catch (Exception $e) {
            if (is_array($e->getMessage())) {
                $information = $this->statusError($e->getMessage());
                return $information;
            } else {
                $information = $this->statusError(array($e->getMessage()));
                return $information;
            }
        }
    }

    public function getNotificationList($data, $device_id) {
		$existedDevice = $this->getCollection()->addFieldToFilter('device_token',$data->device_token)->getFirstItem();
		$notificationList = array();
		if ($existedDevice->getId()) {
			$this->setId($existedDevice->getId());
			$historyList = Mage::getModel('simiconnector/history')->getCollection()
							->addFieldToFilter('status','1')
							->setOrder('history_id','desc');
			foreach ($historyList as $historyItem) {
				if ($historyItem->getData('devices_pushed') && $historyItem->getData('notice_id')) {
					if (in_array($existedDevice->getId(), explode(",", $historyItem->getData('devices_pushed')))){
						$imagesize = getimagesize($historyItem->getData('image_url'));
						$notificationList[] = array('id'=>$historyItem->getData('history_id'),
							'notice_title'=>$historyItem->getData('notice_title'),
							'notice_url'=>$historyItem->getData('notice_url'),
							'notice_content'=>$historyItem->getData('notice_content'),						
							'notice_sanbox'=>$historyItem->getData('notice_sanbox'),
							'website_id'=>$historyItem->getData('website_id'),
							'type'=>$historyItem->getData('type'),
							'category_id'=>$historyItem->getData('category_id'),
							'product_id'=>$historyItem->getData('product_id'),						
							'image_url'=>$historyItem->getData('image_url'),
							'location'=>$historyItem->getData('location'),
							'distance'=>$historyItem->getData('distance'),
							'address'=>$historyItem->getData('address'),
							'city'=>$historyItem->getData('city'),
							'country'=>$historyItem->getData('country'),
							'zipcode'=>$historyItem->getData('zipcode'),
							'state'=>$historyItem->getData('state'),
							'show_popup'=>$historyItem->getData('show_popup'),
							'notice_type'=>$historyItem->getData('notice_type'),							
							'created_time'=>$historyItem->getData('created_time'),
							'notice_id'=>$historyItem->getData('notice_id'),
							'status'=>$historyItem->getData('status'),
							'width'=>$imagesize[0],
							'height'=>$imagesize[1])
							;
					}
				}
			}		
		}
		
		try {
            $information = $this->statusSuccess();
			$information['data'] = $notificationList;
            return $information;
        } catch (Exception $e) {
            if (is_array($e->getMessage())) {
                $information = $this->statusError($e->getMessage());				
                return $information;
            } else {
                $information = $this->statusError(array($e->getMessage()));
                return $information;
            }
        }
    }
    
    public function getAddress($lat, $lng) {
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
            return $addresses;
        } else {
            return false;
        }
    }

}
