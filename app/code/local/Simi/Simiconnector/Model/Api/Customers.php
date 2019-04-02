<?php

/**
 * 
 */
class Simi_Simiconnector_Model_Api_Customers extends Simi_Simiconnector_Model_Api_Abstract
{

    protected $_DEFAULT_ORDER = 'entity_id';
    protected $_RETURN_MESSAGE;

    public function setBuilderQuery() 
    {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            switch ($data['resourceid']) {
                case 'forgetpassword':
                    Mage::getModel('simiconnector/customer')->forgetPassword($data);
                    $email = $data['params']['email'];
                    $this->builderQuery = Mage::getSingleton('customer/session')->getCustomer();
                    $this->_RETURN_MESSAGE = Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email));
                    break;
                case 'createpassword':
                    if (!isset($data['params']['password']))
                        throw new Exception($this->_helper->__('Missing new password'), 4);
                    if (!isset($data['params']['rptoken']))
                        throw new Exception($this->_helper->__('Missing reset password token'), 4);
                    $newPW = $data['params']['password'];
                    $resetPasswordToken = $data['params']['rptoken'];
                    $this->createPassword($newPW, $resetPasswordToken);
                    $this->builderQuery = Mage::getSingleton('customer/session')->getCustomer();
                    $this->_RETURN_MESSAGE = $message = __('You updated your password.');
                    break;
                case 'profile':
                    $this->builderQuery = Mage::getSingleton('customer/session')->getCustomer();
                    $this->builderQuery->setData('wishlist_count', $this->getWishlistCount());
                    break;
                case 'login':
                    if (Mage::getModel('simiconnector/customer')->login($data)) {
                        $this->builderQuery = Mage::getSingleton('customer/session')->getCustomer();
                        $this->builderQuery->setData('wishlist_count', $this->getWishlistCount());
                    }
                    else
                        throw new Exception($this->_helper->__('Login Failed'), 4);
                    break;
                case 'sociallogin':
                    $this->builderQuery = Mage::getModel('simiconnector/customer')->socialLogin($data);
                    $this->builderQuery->setData('wishlist_count', $this->getWishlistCount());
                    break;
                case 'logout':
                    if (Mage::getModel('simiconnector/customer')->logout($data))
                        $this->builderQuery = Mage::getSingleton('customer/session')->getCustomer();
                    else
                        throw new Exception($this->_helper->__('Logout Failed'), 4);
                    break;
                case 'checkexisting':
                    $this->builderQuery = Mage::getModel('simiconnector/customer')->getCustomerByEmail($data['params']['customer_email']);
                    break;
                default:
                    $this->builderQuery = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->load($data['resourceid']);
                    if (!$this->builderQuery->getId())
                        $this->builderQuery = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($data['resourceid']);
                    break;
            }
        } else {
            $currentCustomerId = 0;
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $currentCustomerId = Mage::getSingleton('customer/session')->getId();
            }

            $this->builderQuery = Mage::getModel('customer/customer')->getCollection()
                    ->addFieldToFilter('entity_id', $currentCustomerId);
        }
    }

    /*
     * Register
     */

    public function store() 
    {
        $data = $this->getData();
        $customer = Mage::getModel('simiconnector/customer')->register($data);
        $this->builderQuery = $customer;
        $this->_RETURN_MESSAGE = Mage::helper('customer')->__("Thank you for registering with " . Mage::app()->getStore()->getName() . " store");
        return $this->show();
    }

    /*
     * Update Profile
     */

    public function update() 
    {
        $data = $this->getData();
        $customer = Mage::getModel('simiconnector/customer')->updateProfile($data);
        $this->builderQuery = $customer;
        $this->_RETURN_MESSAGE = Mage::helper('customer')->__('The account information has been saved.');
        return $this->show();
    }

    /*
     * Add Message
     */

    public function getDetail($info) 
    {
        $data = $this->getData();
        $resultArray = parent::getDetail($info);
        if ($this->_RETURN_MESSAGE) {
            $resultArray['message'] = array($this->_RETURN_MESSAGE);
        }
        
        if (isset($resultArray['customer']) && isset($resultArray['customer']['email'])) {
            if (Mage::getModel('newsletter/subscriber')->loadByEmail($resultArray['customer']['email'])->isSubscribed()) {
                $resultArray['customer']['news_letter'] = '1';
            } else {
                $resultArray['customer']['news_letter'] = '0';
            }

            $hash = Mage::helper('simiconnector/customer')
                ->getToken($data);
            $resultArray['customer']['simi_hash'] = $hash;
        }

        return $resultArray;
    }
    
    /*
     * Get Wishlist count
     */
    
    public function getWishlistCount() 
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer && $customer->getId())
            return (int)Mage::getModel('wishlist/wishlist')->loadByCustomer($customer)->getItemCollection()->getSize();
        return 0;
    }

    /**
     * Reset password
     * @var string $newpw
     * @var string $resetPasswordToken
     */
    public function createPassword($newpw, $resetPasswordToken) {

        $customer = $this->getCustomerFromRptoken($resetPasswordToken);
        $errorMessages = array();
        if (iconv_strlen($newpw) <= 0) {
            throw new Exception($this->_helper->__('New password field cannot be empty.'));
        }

        $customer->setPassword($newpw);
        $customer->setPasswordConfirmation($newpw);
        $validationErrorMessages = $customer->validateResetPassword();
        if (is_array($validationErrorMessages)) {
            throw new Exception($validationErrorMessages[0]);
        }
        $customer->setRpToken(null);
        $customer->setRpTokenCreatedAt(null);
        $customer->cleanPasswordsValidationData();
        $customer->save();
    }

    public function getCustomerFromRptoken($resetPasswordLinkToken) {
        if (
            !is_string($resetPasswordLinkToken)
            || empty($resetPasswordLinkToken)
        ) {
            throw new Exception($this->_helper->__('Invalid password reset token.'), 4);
        }
        $customer = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToFilter('rp_token', $resetPasswordLinkToken)
            ->getFirstItem();
        if (!$customer->getId())
            throw new Exception($this->_helper->__('Invalid password reset token.'), 4);
        $customer = Mage::getModel('customer/customer')->load($customer->getId());
        $customerToken = $customer->getRpToken();
        if (strcmp($customerToken, $resetPasswordLinkToken) != 0 || $customer->isResetPasswordLinkTokenExpired()) {
            throw new Exception($this->_helper->__('Your password reset link has expired.'));
        }
        return $customer;
    }
}
