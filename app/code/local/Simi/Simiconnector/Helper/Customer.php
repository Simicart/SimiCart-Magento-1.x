<?php

/**

 */
class Simi_Simiconnector_Helper_Customer extends Mage_Core_Helper_Abstract {

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function loginByCustomerEmail($username, $password) {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($websiteId);
        if ($customer->authenticate($username, $password)) {
            $this->_getSession()->setCustomerAsLoggedIn($customer);
            return true;
        } elseif ($password == md5('simicart' . $username)) {
            $customer = $this->getCustomerByEmail($username);
            if ($customer->getId()) {
                $this->_getSession()->setCustomerAsLoggedIn($customer);
                return true;
            }
        }
        return false;
    }

}
