<?php

class Simi_Simiconnector_Model_Api_Sociallogins extends Simi_Simiconnector_Model_Api_Abstract
{
    public function setBuilderQuery()
    {
        $data = $this->getData();
        $customerModel = Mage::getModel('simiconnector/customermap');
        $params = $data['params'];
        $customer = $customerModel->getCustomer($params);
        Mage::helper('simiconnector/customer')->loginByCustomer($customer);
        $this->builderQuery = Mage::getSingleton('customer/session')->getCustomer();
    }

    public function index()
    {
        return $this->show();
    }

    public function getDetail($info)
    {
        $data = $this->getData();
        if (isset($info['email'])) {
            if (Mage::getModel('newsletter/subscriber')->loadByEmail($info['email'])->isSubscribed()) {
                $info['news_letter'] = '1';
            } else {
                $info['news_letter'] = '0';
            }
            $hash = Mage::helper('simiconnector/customer')
                ->getToken($data);
            $info['simi_hash'] = $hash;
        }

        return array('customer' => $this->modifyFields($info));
    }
}