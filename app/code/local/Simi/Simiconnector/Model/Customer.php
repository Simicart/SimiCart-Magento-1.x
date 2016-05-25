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
            $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email);
            if ($customer->getId()) {
                $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                $customer->sendPasswordResetConfirmationEmail();
            } else {
                $information = $this->statusError(array(Mage::helper('customer')->__('Customer is not exist')));
                return $information;
            }
        }
    }

    public function login($data) {
        return Mage::helper('simiconnector/customer')->loginByCustomerEmail($data['params']['email'], $data['params']['password']);
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
            throw new Exception($this->_helperCustomer()->__('Account is already exist'), 4);
        }

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

        if (isset($data->suffix) && $data->suffix) {
            $customer->setSuffix($data->suffix);
        }

        $customer->setPassword($data->password);
        $customer->save();
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
        if (isset($data->day) && $data->day != "") {
            $birthday = $data->year . "-" . $data->month . "-" . $data->day;
            $customer->setDob($birthday);
        }
        if (isset($data->taxvat) && $data->taxvat) {
            $customerData['taxvat'] = $data->taxvat;
        }
        if (isset($data->gender) && $data->gender) {
            $customerData['gender'] = $data->gender;
        }
        if (isset($data->prefix) && $data->prefix) {
            $customerData['prefix'] = $data->prefix;
        }
        if (isset($data->suffix) && $data->suffix) {
            $customerData['suffix'] = $data->suffix;
        }

        if (version_compare(Mage::getVersion(), '1.4.2.0', '<') === true) {
            $customer = Mage::getModel('customer/customer')
                    ->setId($this->_getSession()->getCustomerId())
                    ->setWebsiteId($this->_getSession()->getCustomer()->getWebsiteId());
            $fields = Mage::getConfig()->getFieldset('customer_account');
            foreach ($fields as $code => $node) {
                if ($node->is('update') && isset($customerData[$code])) {
                    $customer->setData($code, $customerData[$code]);
                }
            }
        } else {
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('customer_account_edit')
                    ->setEntity($customer);
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                if (is_array($customerErrors))
                    throw new Exception($customerErrors[0], 4);
                else
                    throw new Exception($customerErrors, 4);
            } else {
                $customerForm->compactData($customerData);
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
        $customerErrors = $customer->validate();
        if (is_array($customerErrors))
            throw new Exception($this->_helperCustomer()->__('Invalid profile information'), 4);
        $customer->setConfirmation(null);
        $customer->save();
        $this->_getSession()->setCustomer($customer);
        return $customer;
    }

}
