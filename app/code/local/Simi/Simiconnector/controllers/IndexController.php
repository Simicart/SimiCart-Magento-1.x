<?php

class Simi_Simiconnector_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function installDBAction() {
        $setup = new Mage_Core_Model_Resource_Setup('core_setup');
        $installer = $setup;
        $installer->startSetup();
        $installer->run(" 
            
    DROP TABLE IF EXISTS {$installer->getTable('simivideo_videos')};
        CREATE TABLE {$installer->getTable('simivideo_videos')} (
      `video_id` int(11) unsigned NOT NULL auto_increment,
      `video_url` varchar(255) NULL default '',
      `video_key` varchar(255) NULL default '',
      `video_title` varchar(255) NULL default '',
      `product_ids` text NULL default '',
      `status` int(11) NULL, 
      PRIMARY KEY (`video_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

          ");
        $installer->endSetup();
        echo 'success';
    }

}
