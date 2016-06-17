<?php

/**

 */
class Simi_Simiconnector_Helper_Customer extends Mage_Core_Helper_Abstract {

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function renewCustomerSesssion($data) {
        if (($data['resource'] == 'customers') && (($data['resourceid'] == 'login')||($data['resourceid'] == 'sociallogin')))
            return;
        if ((!$data['params']['email']) && ($data['params']['password']))
            return;
        try {
            $this->loginByCustomerEmail($data['params']['email'], $data['params']['password']);
        } catch (Exception $e) {
            
        }
    }
    
    public function socialLoginSesssion($data) {
        die;
        if (!$data['params']['email'])
            return false;
       
    }

    public function loginByCustomerEmail($username, $password) {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($websiteId);
        if ($password == md5(Mage::getStoreConfig('simiconnector/general/secret_key') . $username)) {
            $customer = $this->getCustomerByEmail($username);
            if ($customer->getId()) {
                $this->_getSession()->setCustomerAsLoggedIn($customer);
                return true;
            }
        }
        else if ($customer->authenticate($username, $password)) {
            $this->_getSession()->setCustomerAsLoggedIn($customer);
            return true;
        }
        return false;
    }

    public function getCustomerByEmail($email) {
        return Mage::getModel('customer/customer')->getCollection()
                        ->addFieldToFilter('email', $email)
                        ->getFirstItem();
    }

}
