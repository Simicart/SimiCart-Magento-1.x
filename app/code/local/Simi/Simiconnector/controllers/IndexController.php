<?php

class Simi_Simiconnector_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function checkInstallAction(){
        $arr = array();
        $arr['is_install'] = "1";
        $key = $this->getRequest()->getParam('key');
        if ($key == null || $key == '')
        {
            $arr["website_key"] = "0";

        }else{
            $keySecret = md5 (Mage::getStoreConfig('simiconnector/general/secret_key'));
            if (strcmp($key, $keySecret) == 0)
                $arr["website_key"] = "1";
            else
                $arr["website_key"] = "0";
        }
        echo json_encode($arr);
        exit();
    }

    public function installDBAction() {
        $setup = new Mage_Core_Model_Resource_Setup('core_setup');
        $installer = $setup;
        $installer->startSetup();
        
        $installer->run("
    DROP TABLE IF EXISTS {$installer->getTable('simiconnector_visibility')};
   
    CREATE TABLE {$installer->getTable('simiconnector_visibility')} (
        `entity_id` int(11) unsigned NOT NULL auto_increment,
        `content_type` tinyint(4) NOT NULL default '0',
        `item_id` int(10) NOT NULL default '0',
        `store_view_id` varchar(255) NULL default '0', 
        PRIMARY KEY (`entity_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;  
");
        $installer->endSetup();
        echo 'success';
    }
    
    public function updateDB2Action() {
        $setup = new Mage_Core_Model_Resource_Setup();
        $installer = $setup;
        $installer->startSetup();
        $installer->run("
            
    	DROP TABLE IF EXISTS {$installer->getTable('simiconnector_taskbar')};      

   CREATE TABLE {$installer->getTable('simiconnector_taskbar')} (
      `taskbar_id` int(11) unsigned NOT NULL auto_increment,
      `storeview_id` int(6) default 0,
      `taskbar_color` varchar(255) default '',	  
      `icon_color` varchar(255) default '',
      `item1_text` varchar(255) default '',
      `item1_image` varchar(255) default '',	  
      `item1_type` varchar(255) default '',
	  `item2_text` varchar(255) default '',
      `item2_image` varchar(255) default '',	  
      `item2_type` varchar(255) default '',
	  `item3_text` varchar(255) default '',
      `item3_image` varchar(255) default '',	  
      `item3_type` varchar(255) default '',
	  `item4_text` varchar(255) default '',
      `item4_image` varchar(255) default '',	  
      `item4_type` varchar(255) default '',
	  `item5_text` varchar(255) default '',
      `item5_image` varchar(255) default '',	  
      `item5_type` varchar(255) default '',
	  `item6_text` varchar(255) default '',
      `item6_image` varchar(255) default '',	  
      `item6_type` varchar(255) default '',
	  
      PRIMARY KEY (`taskbar_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
        ");
        $installer->endSetup();
        echo "success";
    }

}
