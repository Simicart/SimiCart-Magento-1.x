<?php

/**

 */
class Simi_Simiconnector_Helper_Customer extends Mage_Core_Helper_Abstract {

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function renewCustomerSesssion($data) {
        if ($data['params']['quote_id']) {
            $checkoutsession = Mage::getSingleton('checkout/session');
            $checkoutsession->setQuoteId($data['params']['quote_id']);
        }
        if (($data['resource'] == 'customers') && (($data['resourceid'] == 'login') || ($data['resourceid'] == 'sociallogin')))
            return;
        if ((!$data['params']['email']) || (!$data['params']['password']))
            return;
        try {
            $this->loginByEmailAndPass($data['params']['email'], $data['params']['password']);
        } catch (Exception $e) {
            
        }
    }

    public function loginByEmailAndPass($username, $password) {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($websiteId);
        if ($password == md5(Mage::getStoreConfig('simiconnector/general/secret_key') . $username)) {
            $customer = $this->getCustomerByEmail($username);
            if ($customer->getId()) {
                $this->loginByCustomer($customer);
                return true;
            }
        } else if ($customer->authenticate($username, $password)) {
            $this->loginByCustomer($customer);
            return true;
        }
        return false;
    }

    public function getCustomerByEmail($email) {
        return Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email);
    }

    public function loginByCustomer($customer) {
        $this->_getSession()->setCustomerAsLoggedIn($customer);
    }

}
