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
            DROP TABLE IF EXISTS {$installer->getTable('simibarcode')}; 
    CREATE TABLE {$installer->getTable('simibarcode')} (
    `barcode_id` int(11) unsigned NOT NULL auto_increment,        
    `barcode` varchar(255) default '',  
    `qrcode` varchar(255) default '',  
    `barcode_status` tinyint(3) NOT NULL default '1',
    `product_entity_id` int(11),
    `product_name` varchar(255) default '',
    `product_sku` varchar(255) default '',
    `created_date` datetime,
    UNIQUE (`barcode`),
    UNIQUE (`qrcode`),
    PRIMARY KEY  (`barcode_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8; 
    
    DROP TABLE IF EXISTS {$installer->getTable('connector_device')};   
    
    CREATE TABLE {$installer->getTable('connector_device')} (
        `device_id` int(11) unsigned NOT NULL auto_increment,
        `device_token` varchar(255) NOT NULL UNIQUE,   
        `plaform_id` int (11),
        `website_id` int (11),
        `latitude` varchar(30) NOT NULL default '',
        `longitude` varchar(30) NOT NULL default '',
        `address` varchar(255) NOT NULL default '',
        `city` varchar(255) NOT NULL default '',
        `country` varchar(255) NOT NULL default '',
        `zipcode` varchar(25) NOT NULL default '',
        `state` varchar(255) NOT NULL default '',
        `created_time` datetime NOT NULL default '0000-00-00 00:00:00',
        `is_demo` tinyint(1) NULL default '3',
        `user_email` varchar(255) NOT NULL default '',
        `app_id` varchar(255) NOT NULL default '',
        `build_version` varchar(255) NOT NULL default '',
        PRIMARY KEY (`device_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
          ");
        $installer->endSetup();
        echo 'success';
    }

}
