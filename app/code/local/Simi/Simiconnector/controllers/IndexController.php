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
