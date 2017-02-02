<?php

/**
 * 
 */
class Simi_Simiconnector_Model_Customer extends Mage_Core_Model_Abstract {

    protected function _helperCustomer() {
        return Mage::helper('simiconnector/customer');
    }

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function getCustomerByEmail($email) {
        return Mage::getModel('customer/customer')->getCollection()
                        ->addFieldToFilter('email', $email)
                        ->getFirstItem();
    }

    public function forgetPassword($data) {
        $data = $data['params'];
        $email = $data['email'];
        if (is_null($email)) {
            throw new Exception($this->_helperCustomer()->__('No email was sent'), 4);
        } else {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                throw new Exception($this->_helperCustomer()->__('Invalid email address.'), 4);
            }
            $customer = Mage::helper('simiconnector/customer')->getCustomerByEmail($email);
            if ($customer->getId()) {
                $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                $customer->sendPasswordResetConfirmationEmail();
            } else {
                throw new Exception($this->_helperCustomer()->__('Customer is not exist'));
            }
        }
    }

    public function login($data) {
        return Mage::helper('simiconnector/customer')->loginByEmailAndPass($data['params']['email'], $data['params']['password']);
    }

    public function logout() {
        $this->_getSession()->logout()
                ->setBeforeAuthUrl(Mage::getUrl());
        return true;
    }

    public function register($data) {
        $data = $data['contents'];
        $message = array();
        $checkCustomer = $this->getCustomerByEmail($data->email);
        if ($checkCustomer->getId()) {
            throw new Exception($this->_helperCustomer()->__('Account already exists'), 4);
        }
        $customer = $this->_createCustomer($data);
        $result = array();
        $result['user_id'] = $customer->getId();
        $session = $this->_getSession();
        if ($customer->isConfirmationRequired()) {
            $app = Mage::app();
            $store = $app->getStore();
            $customer->sendNewAccountEmail(
                    'confirmation', $session->getBeforeAuthUrl(), $store->getId()
            );
            throw new Exception($this->_helperCustomer()->__('Account confirmation is required. Please, check your email.'), 4);
        } else {
            $customer->setConfirmation(null);
            $customer->save();
            $customer->sendNewAccountEmail();
        }
        return $customer;
    }

    public function updateProfile($data) {
        $data = $data['contents'];
        $result = array();
        $currPass = $data->old_password;
        $newPass = $data->new_password;
        $confPass = $data->com_password;

        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($data->email);

        $customerData = array(
            'firstname' => $data->firstname,
            'lastname' => $data->lastname,
            'email' => $data->email,
        );
        if (version_compare(Mage::getVersion(), '1.4.2.0', '<') === true) {
            $customer = Mage::getModel('customer/customer')
                    ->setId($this->_getSession()->getCustomerId())
                    ->setWebsiteId($this->_getSession()->getCustomer()->getWebsiteId());
        }
        $fields = Mage::getConfig()->getFieldset('customer_account');
        foreach ($fields as $code => $node) {
            if ($node->is('update') && isset($customerData[$code])) {
                $customer->setData($code, $customerData[$code]);
            }
        }

        if ($data->change_password == 1) {
            $customer->setChangePassword(1);
            $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
            if (Mage::helper('core/string')->strpos($oldPass, ':')) {
                list($_salt, $salt) = explode(':', $oldPass);
            } else {
                $salt = false;
            }
            if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                if (strlen($newPass)) {
                    $customer->setPassword($newPass);
                    $customer->setConfirmation($confPass);
                    $customer->setPasswordConfirmation($confPass);
                } else {
                    throw new Exception($this->_helperCustomer()->__('New password field cannot be empty'), 4);
                }
            } else {
                throw new Exception($this->_helperCustomer()->__('Invalid current password'), 4);
            }
        }

        if (isset($data->taxvat)) {
            $customer->setTaxvat($data->taxvat);
        }

        if (isset($data->day) && $data->day != "") {
            $birthday = $data->year . "-" . $data->month . "-" . $data->day;
            $customer->setDob($birthday);
        }

        if (isset($data->gender) && $data->gender) {
            $customer->setGender($data->gender);
        }
        if (isset($data->prefix) && $data->prefix) {
            $customer->setPrefix($data->prefix);
        }

        if (isset($data->middlename) && $data->middlename) {
            $customer->setMiddlename($data->middlename);
        }

        if (isset($data->suffix) && $data->suffix) {
            $customer->setSuffix($data->suffix);
        }

        $customerErrors = $customer->validate();
        if (is_array($customerErrors))
            throw new Exception($this->_helperCustomer()->__('Invalid profile information'), 4);
        $customer->setConfirmation(null);
        $customer->save();
        $this->_getSession()->setCustomer($customer);
        return $customer;
    }

    /*
     * Social Login
     * @param 
     * $data - Object with at least:
     * $data->firstname
     * $data->lastname
     * $data->email
     */

    public function socialLogin($data) {
        $data = (object) $data['params'];
        if (!isset($data->password) || !Mage::helper('simiconnector/customer')->validateSimiPass($data->email, $data->password))
            throw new Exception($this->_helperCustomer()->__('Password is not Valid'), 4);
        if (!$data->email)
            throw new Exception($this->_helperCustomer()->__('Cannot Get Your Email'), 4);
        $customer = Mage::helper('simiconnector/customer')->getCustomerByEmail($data->email);
        if (!$customer->getId()) {
            if (!$data->firstname)
                $data->firstname = $this->_helperCustomer()->__('Firstname');
            if (!$data->lastname)
                $data->lastname = $this->_helperCustomer()->__('Lastname');
            $customer = $this->_createCustomer($data);
            $customer->setConfirmation(null);
            $customer->save();
            $customer->sendPasswordReminderEmail();
        }
        Mage::helper('simiconnector/customer')->loginByCustomer($customer);
        return $customer;
    }

    /*
     * Create Customer
     * @param 
     * $data - Object with at least:
     * $data->firstname
     * $data->lastname
     * $data->email
     * $data->password
     */

    private function _createCustomer($data) {
        $customer = Mage::getModel('customer/customer')
                ->setFirstname($data->firstname)
                ->setLastname($data->lastname)
                ->setEmail($data->email);
        if (isset($data->day) && $data->day != "") {
            $birthday = $data->year . "-" . $data->month . "-" . $data->day;
            $customer->setDob($birthday);
        }

        if (isset($data->taxvat)) {
            $customer->setTaxvat($data->taxvat);
        }

        if (isset($data->gender) && $data->gender) {
            $customer->setGender($data->gender);
        }
        if (isset($data->prefix) && $data->prefix) {
            $customer->setPrefix($data->prefix);
        }

        if (isset($data->middlename) && $data->middlename) {
            $customer->setMiddlename($data->middlename);
        }

        if (isset($data->suffix) && $data->suffix) {
            $customer->setSuffix($data->suffix);
        }
        if (!$data->password)
            $data->password = $customer->generatePassword();
        $customer->setPassword($data->password);
        $customer->save();

        if (isset($data->news_letter) && ($data->news_letter == '1'))
            Mage::getModel('newsletter/subscriber')->subscribe($data->email);
        else
            Mage::getModel('newsletter/subscriber')->loadByEmail($data->email)->unsubscribe();
        return $customer;
    }

}
