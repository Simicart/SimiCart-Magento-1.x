<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 11/7/16
 * Time: 1:57 PM
 */
class Simi_Simiconnector_Helper_Cloud extends Mage_Core_Helper_Abstract
{
    protected $_json=null;
    public function __construct(){
        if($this->_json == null){
            $this->getConfigfromCloud();
        }
    }

    public function getConfigfromCloud(){
        ini_set('display_errors', 1);
        $token = (String )Mage::getStoreConfig('simiconnector/general/token_key');
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, "https://api.jajahub.com/rest/app-configs/");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-type: application/json',
            'Authorization: Bearer '.$token,
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
        $response = curl_exec($ch);
        curl_close($ch);
        $this->_json = json_decode($response, true);
    }

    //default, matrix, zara
    public function getThemeLayout(){
        $data = $this->_json;

        if(isset($data['errors']))
            return 'matrix';

        if(isset($data['app-configs'][0]['layout'])){
            return $data['app-configs'][0]['layout'];
        }
        return 'matrix';

    }
}