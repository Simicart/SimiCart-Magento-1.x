<?php

class Simi_Simiconnector_IndexController extends Mage_Core_Controller_Front_Action
{

    public function updateCountPurchaseForDeviceAction()
    {
        $collection = Mage::getModel('simiconnector/device')->getCollection();
        $test_data = array();
        foreach ($collection as $device) {

            $id = $device->getId();

            $customer_email = $device['user_email'];

            if ($customer_email) {
                $orders = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToFilter('customer_email', $customer_email);
                $size = $orders->getSize();

                $deviceModel = Mage::getModel('simiconnector/device')->load($id);
                if ($deviceModel->getId()) {
                    $item_data = array();
                    $item_data['email'] = $customer_email;
                    $item_data['size'] = $size;
                    $test_data[] = $item_data;

                    $deviceModel->setCountPurchase($size);
                    $deviceModel->save();

                }
            }
        }

        echo json_encode($test_data);

    }

    public function updateDBAction(){
        $setup = new Mage_Core_Model_Resource_Setup('core_setup');
        $installer = $setup;
        $installer->startSetup();
        $installer->getConnection()->addColumn($installer->getTable('simiconnector_device'), 'count_purchase', 'int(11)');
        $installer->endSetup();
        echo 'OK ADD COLUMN';
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function checkInstallAction()
    {
        $arr = array();
        $arr['is_install'] = "1";
        $key = $this->getRequest()->getParam('key');
        if ($key == null || $key == '') {
            $arr["website_key"] = "0";
        } else {
            $keySecret = md5(Mage::getStoreConfig('simiconnector/general/secret_key'));
            if (strcmp($key, $keySecret) == 0)
                $arr["website_key"] = "1";
            else
                $arr["website_key"] = "0";
        }
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($arr)
        );
    }

    public function syncKeysAction()
    {
        if (!Mage::getStoreConfig('simiconnector/general/secret_key') || Mage::getStoreConfig('simiconnector/general/secret_key')=='') {
            $secretKey = $this->getRequest()->getParam('secret', null);
            $publicKey = $this->getRequest()->getParam('public', null);
            $result = array();
            if (!$secretKey || !$publicKey) {
                $result['status'] = '0';
                $result['message'] = 'Missing key';
                return $this->getResponse()->setBody(json_encode($result));
            }

            try {
                $secretKey = Mage::helper('core')->encrypt($secretKey);
                $publicKey = Mage::helper('core')->encrypt($publicKey);
                $configModel = Mage::getModel('core/config');
                $configModel->saveConfig('simiconnector/general/secret_key', $secretKey);
                $configModel->saveConfig('simiconnector/general/token_key', $publicKey);
                $configModel->cleanCache();
                $result['status'] = '1';
                $result['message'] = 'Successfully';
            } catch (Exception $e) {
                $result['status'] = '0';
                $result['message'] = $e->getMessage();
            }
        } else {
            $result = array();
            $result['status'] = '0';
            $result['message'] = 'Already Filled';
        }

        return $this->getResponse()->setBody(json_encode($result));
    }

    /*
    public function installDBAction()
    {
        $setup = new Mage_Core_Model_Resource_Setup('core_setup');
        $installer = $setup;
        $installer->startSetup();

        $installer->run(
            "
    
        "
        );
        $installer->endSetup();
        echo 'success';
    }
    */


    public function checkPortAction() {
        $host = 'gateway.sandbox.push.apple.com';
        $port = 2195;
        $hostip = @gethostbyname($host);
        
        if ($hostip == $host) {
            $config = array('2195_port_status' => 'Server is down or does not exist');
        } else {
            if (!$x = @fsockopen($hostip, $port, $errno, $errstr, 5)) {
                $config = array('2195_port_status' => 'closed');
            } else {
                $config = array('2195_port_status' => 'opened');
                if ($x) {
                    @fclose($x);
                }
            }
        }
        
        // Set the response body / contents to be the JSON data
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($config)
        );
    }
}
