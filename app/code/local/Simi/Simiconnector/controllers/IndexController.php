<?php

class Simi_Simiconnector_IndexController extends Mage_Core_Controller_Front_Action
{

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
        echo json_encode($arr);
        exit();
    }

    public function syncKeysAction()
    {
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

        return $this->getResponse()->setBody(json_encode($result));
    }

    public function installDBAction()
    {
        $setup = new Mage_Core_Model_Resource_Setup('core_setup');
        $installer = $setup;
        $installer->startSetup();

        $installer->run("
    
        ");
        $installer->endSetup();
        echo 'success';
    }


    public function checkPortAction()
    {
        $host = 'gateway.sandbox.push.apple.com';
        $port = 2195;
        $hostip = @gethostbyname($host);

        if ($hostip == $host) {
            echo "Server is down or does not exist";
        } else {
            if (!$x = @fsockopen($hostip, $port, $errno, $errstr, 5)) {
                echo "Port $port is closed.";
            } else {
                echo "Port $port is open.";
                if ($x) {
                    @fclose($x);
                }
            }
        }
        exit();
    }
}
