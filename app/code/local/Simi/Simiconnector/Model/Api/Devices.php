<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Devices extends Simi_Simiconnector_Model_Api_Abstract
{

    protected $_DEFAULT_ORDER = 'device_id';

    public function setBuilderQuery() 
    {
    }

    public function store() 
    {
        $data = $this->getData();
        $device = Mage::getModel('simiconnector/device');
        $device->saveDevice($data);
        $this->builderQuery = $device;
        return $this->show();
    }

    public function update(){
        $data = $this->getData();
        $parameter = (array) $data['contents'];
        $notice_id = $parameter['notice_id'];
        if($notice_id){
            $notice = Mage::getModel('simiconnector/siminotification')->load($notice_id);
            if($notice->getId()){
                try {
                    $click = $notice->getClick();
                    $notice->setClick($click + 1);

                    $notice->save();

                }catch (Exception $e){

                }
            }
        }


        $notice_history_id = $parameter['notice_history_id'];

        if($notice_history_id){
            $history = Mage::getModel('simiconnector/history')->load($notice_history_id);
            if($history->getId()){
                try {
                    $click = $history->getClick();
                    $history->setClick($click + 1);

                    $history->save();
                    return $this->getDetail(array('status' => '1', 'message' => 'Save successfully'));
                }catch (Exception $e){
                    return $this->getDetail(array('status'=>'0','message'=>$e->getMessage()));
                }
            }else{

            }

        }

        return $this->getDetail(array('status'=>'0','message'=>'Invalid notice id.'));




    }

}
