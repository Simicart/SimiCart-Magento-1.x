<?php

class Simi_Simiconnector_Adminhtml_Simiconnector_DeviceController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('simiconnector/device')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Devices Manager'), Mage::helper('adminhtml')->__('Devices Manager'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * view and edit item action
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('simiconnector/device')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data))
                $model->setData($data);

            Mage::register('device_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('simiconnector/device');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Device Manager'), Mage::helper('adminhtml')->__('Device Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Device News'), Mage::helper('adminhtml')->__('Device News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('simiconnector/adminhtml_device_edit'))
                ->_addLeft($this->getLayout()->createBlock('simiconnector/adminhtml_device_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('simiconnector')->__('Device does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * delete item action
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('simiconnector/device');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Device was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * mass delete item(s) action
     */
    public function massDeleteAction() {
        $deviceIds = $this->getRequest()->getParam('siminotification');

        if (!is_array($deviceIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($deviceIds as $deviceId) {
                    $device = Mage::getModel('simiconnector/device')->load($deviceId);
                    $device->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d device(s) were successfully deleted', count($bannerIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }


    public function filterStateCityAction(){
        $is_state = $this->getRequest()->getParam('is_state');
        $country_code = $this->getRequest()->getParam('country_code');
        $city_code = $this->getRequest()->getParam('city_code');
        if($is_state ) {
            if($country_code) {
                $states = Mage::helper('simiconnector/siminotification')->getListState($country_code);
                if (count($states) > 0) {
                    $states_response = "<option value=''> </option>";
                    foreach ($states as $key => $state) {
                        $states_response .= "<option value='" . $key . '_' . $state . "'>" . $state . " </option>";
                    }
                    echo $states_response;
                }
            }
            echo '';
        }
        else {
            $array = explode('_',$city_code);
            if(count($array)){
                $city_code = $array[0];
            }
            $counties =Mage::getModel('romcity/romcity')->getCollection()->addFieldToFilter('country_id', $country_code)
                ->addFieldToFilter('region_id', $city_code);
            if(count($counties) > 0){
                $counties_response = "<option value=''></option>";
                foreach ($counties as $county){
                    $full_city_name = $county->getData('cityname');
                    $city_name = trim( str_replace('Quận','',$full_city_name));
                    $counties_response  .= "<option value='".$city_name."'>".$full_city_name." </option>";
                }
                echo $counties_response;
            }
            echo '';
        }
    }

}
