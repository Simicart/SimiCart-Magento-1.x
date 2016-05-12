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
        CREATE TABLE {$installer->getTable('connector_cms')} (
            `cms_id` int(11) unsigned NOT NULL auto_increment,
            `cms_title` varchar(255) NULL, 
            `cms_image` varchar(255) NULL default '', 
            `cms_content` text NULL default '',  
            `cms_status` tinyint(4) NOT NULL default '1',
            `website_id` smallint(5) NULL,
            PRIMARY KEY (`cms_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
          ");
        $installer->endSetup();
        echo 'success';
    }

}
