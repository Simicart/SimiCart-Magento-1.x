<?php

class Simi_Simiconnector_Model_Customermap extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('simiconnector/customermap');
    }

    public function createCustomer($params)
    {
        $email = isset($params['email'])?$params['email']:$params['uid'].$params['providerId'].'@simisocial.com';
        $firstName = isset($params['firstname'])?$params['firstname']:' ';
        $lastName = isset($params['lastname'])?$params['lastname']:' ';

        $existedCustomer =Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->loadByEmail($params['email']);
        if ($existedCustomer->getId())
            throw new Exception(Mage::helper('simiconnector')->__('Cannot create new customer account'), 4);

        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setEmail($email);

        $password = 'simipassword'
            . rand(pow(10, 9), pow(10, 10)) . substr(md5(microtime()), rand(0, 26), 5);

        if (isset($params['hash']) && $params['hash'] !== '') {
            $password = $params['hash'];
        }

        $customer->setPassword($password);
        $customer->save();

        $dataMap = array(
            'customer_id' => $customer->getId(),
            'social_user_id' => $params['uid'],
            'provider_id' => $params['providerId']
        );

        $this->setData($dataMap)->save();
        return $customer;
    }

    /*
     * @params - array [providerId, uid, email (opt.), firstname (opt.), lastname (opt.), hash (opt.)]
     */
    public function getCustomer($params)
    {
        $providerId = $params['providerId'];
        $uid = $params['uid'];
        $customerMap = $this->getCollection()
            ->addFieldToFilter('provider_id', array('eq' => $providerId))
            ->addFieldToFilter('social_user_id', array('eq' => $uid))
            ->getFirstItem();
        if ($customerMap->getId()) {
            return Mage::getModel('customer/customer')->load($customerMap->getCustomerId());
        } else {
            return $this->createCustomer($params);
        }
    }
}