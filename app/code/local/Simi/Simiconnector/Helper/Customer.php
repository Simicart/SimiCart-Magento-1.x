<?php

/**
 */
class Simi_Simiconnector_Helper_Customer extends Mage_Core_Helper_Abstract
{

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function renewCustomerSesssion($data)
    {
        $loginParams = array();

        if (isset($data['params']['customer_access_token'])) {
            $loginInfo = $this->prepareDataLogin($data['params']['customer_access_token']);
            $loginParams['email'] = $loginInfo['email'];
            $loginParams['password'] = $loginInfo['password'];
        }

        if (isset($data['params']['quote_id']) && $data['params']['quote_id']) {
            $checkoutsession = Mage::getSingleton('checkout/session');
            $checkoutsession->setQuoteId($data['params']['quote_id']);
        }
        if (($data['resource'] == 'customers') && (($data['resourceid'] == 'login') || ($data['resourceid'] == 'sociallogin')))
            return;

        if (isset($data['contents_array']['customer_access_token'])) {
            $loginInfo = $this->prepareDataLogin($data['contents_array']['customer_access_token']);
            $loginParams['email'] = $loginInfo['email'];
            $loginParams['password'] = $loginInfo['password'];
        }
        if ((!isset($loginParams['email'])) || (!isset($loginParams['password'])))
            return;

        if ((Mage::getSingleton('customer/session')->isLoggedIn()) && (Mage::getSingleton('customer/session')->getCustomer()->getEmail() == $data['params']['email']))
            return;

        try {
            $this->loginByEmailAndPass($loginParams['email'], $loginParams['password']);
        } catch (Exception $e) {

        }
    }

    public function loginByEmailAndPass($username, $password)
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId);
        if ($this->validateSimiPass($username, $password)) {
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

    public function getCustomerByEmail($email)
    {
        return Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email);
    }

    public function loginByCustomer($customer)
    {
        $this->_getSession()->setCustomerAsLoggedIn($customer);
    }

    public function validateSimiPass($username, $password)
    {
        if ($password == md5(Mage::getStoreConfig('simiconnector/general/secret_key') . $username)) {
            return true;
        }
        return false;
    }


    public function prepareDataLogin($data)
    {
        $result = array();

        if (!$data) {
            throw  new Exception($this->__('Invalid email address or password. Please try again'), 4);
        }

        $secretKey = Mage::getStoreConfig('simiconnector/general/secret_key');
        $total = 0;

        for ($i = 0; $i < strlen($secretKey); $i++) {
            if (is_numeric($secretKey[$i])) {
                $total += $secretKey[$i];
            }
        }
        $total = $total * 2;
        $total = str_split((string)$total);
        $data = base64_decode($data);
        $data = explode(':', $data);
        $result['email'] = base64_decode($data[0]);
        $simiPassword = base64_decode($data[1]);
        $simiPassword = substr($simiPassword, $total[0],strlen($simiPassword) - strlen($secretKey));
        $result['password'] = base64_decode($simiPassword);
        return $result;
    }
}
