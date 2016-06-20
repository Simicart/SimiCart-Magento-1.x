<?php

/**
 * Created by PhpStorm.
 * User: Scott
 * Date: 6/20/2016
 * Time: 2:09 PM
 */
class Simi_Simiconnector_Helper_Plugins_Instantcontact extends Mage_Core_Helper_Abstract
{

    public function getConfig($value) {
        return Mage::getStoreConfig("simiconnector/instant_contact/" . $value);
    }
    public function isEnabled()
    {
        if($this->getConfig('enable')==1){
            return true;
        }
        return false;
    }

    public function getContacts(){
        $data = array(
            'email' => $this->_getEmails(),
            'phone' => $this->_getPhoneNumbers(),
            'message' => $this->_getMessageNumbers(),
            'website' => $this->getConfig("website"),
            'style' => $this->getConfig("style"),
            'activecolor' => $this->getConfig("icon_color")
        );

        return $data;
    }

    public function _getPhoneNumbers() {
        return explode(",", str_replace(' ', '', $this->getConfig("phone")));
    }

    public function _getMessageNumbers() {
        return explode(",", str_replace(' ', '', $this->getConfig("message")));
    }

    public function _getEmails() {
        $emails = explode(",", str_replace(' ', '', $this->getConfig("email")));
        foreach ($emails as $index=>$email) {
            if(!filter_var($email, FILTER_VALIDATE_EMAIL))
                unset($emails[$index]);
        }
        return $emails;
    }
}