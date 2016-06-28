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
    DROP TABLE IF EXISTS {$installer->getTable('simiconnector_simicategory')};
    DROP TABLE IF EXISTS {$installer->getTable('simiconnector_banner')};
    DROP TABLE IF EXISTS {$installer->getTable('simiconnector_cms')};
    DROP TABLE IF EXISTS {$installer->getTable('simiconnector_device')};          
    DROP TABLE IF EXISTS {$installer->getTable('simiconnector_notice')};
    DROP TABLE IF EXISTS {$installer->getTable('simiconnector_notice_history')};
    DROP TABLE IF EXISTS {$installer->getTable('simiconnector_product_list')};
    DROP TABLE IF EXISTS {$installer->getTable('simiconnector_visibility')};
    DROP TABLE IF EXISTS {$installer->getTable('simiconnector_simibarcode')};
    DROP TABLE IF EXISTS {$installer->getTable('simiconnector_videos')};


    CREATE TABLE {$installer->getTable('simiconnector_simicategory')} (
        `simicategory_id` int(11) unsigned NOT NULL auto_increment,
        `simicategory_name` varchar(255),
        `simicategory_filename` varchar(255),
        `simicategory_filename_tablet` varchar(255),
        `category_id` int(8),
        `status` smallint(6) NOT NULL default '0',
        `website_id` int(6) default 0,
        `storeview_id` varchar(255) NULL default '',
        `sort_order` int(6) NULL default '0',
        PRIMARY KEY (`simicategory_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    CREATE TABLE {$installer->getTable('simiconnector_banner')} (
        `banner_id` int(11) unsigned NOT NULL auto_increment,
        `banner_name` varchar(255) NULL, 
        `banner_url` varchar(255) NULL default '',        
        `banner_url_tablet` varchar(255) NULL default '',
        `banner_title` varchar(255) NULL,
        `status` int(11) NULL,  
        `website_id` smallint(5) NULL default 0,
        `type` smallint(5) unsigned default 3,
        `category_id` int(10) unsigned  NOT NULL,
        `product_id` int(10) unsigned  NOT NULL, 
        `sort_order` int(6) NULL default '0',
        PRIMARY KEY (`banner_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
    
    CREATE TABLE {$installer->getTable('simiconnector_cms')} (
        `cms_id` int(11) unsigned NOT NULL auto_increment,
        `cms_title` varchar(255) NULL, 
        `cms_image` varchar(255) NULL default '', 
        `cms_content` text NULL default '',  
        `cms_status` tinyint(4) NOT NULL default '1',
        `website_id` smallint(5) NULL default 0,
        `sort_order` int(6) NULL default '0',
        PRIMARY KEY (`cms_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
    CREATE TABLE {$installer->getTable('simiconnector_product_list')} (
        `productlist_id` int(11) unsigned NOT NULL auto_increment,
        `list_title` varchar(255) NULL, 
        `list_image` varchar(255) NULL default '',         
        `list_image_tablet` varchar(255) NULL default '', 
        `list_type` tinyint(4) NOT NULL default '1',        
        `list_products` text NULL default '',
        `list_status` tinyint(4) NOT NULL default '1',        
        `sort_order` int(6) NULL default '0',
        PRIMARY KEY (`productlist_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
    CREATE TABLE {$installer->getTable('simiconnector_visibility')} (
        `entity_id` int(11) unsigned NOT NULL auto_increment,
        `content_type` tinyint(4) NOT NULL default '0',
        `item_id` tinyint(4) NOT NULL default '0',
        `store_view_id` varchar(255) NULL default '0', 
        PRIMARY KEY (`entity_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;        
    
    CREATE TABLE {$installer->getTable('simiconnector_device')} (
        `device_id` int(11) unsigned NOT NULL auto_increment,
        `device_token` varchar(255) NOT NULL UNIQUE,   
        `plaform_id` int (11),
        `storeview_id` int (11),
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
        
    CREATE TABLE {$installer->getTable('simiconnector_notice')} (
        `notice_id` int(11) unsigned NOT NULL auto_increment,
        `notice_title` varchar(255) NULL default '',    
        `notice_url` varchar(255) NULL default '',    
        `notice_content` text NULL default '',    
        `notice_sanbox` tinyint(1) NULL default '0',
        `website_id` int (11),
        `device_id` int (11),
        `type` smallint(5) unsigned,
        `category_id` int(10) unsigned  NOT NULL,
        `product_id` int(10) unsigned  NOT NULL,
        `image_url` varchar(255) NOT NULL default'',
        `location` varchar(255) NOT NULL default '',
        `distance` varchar(255) NOT NULL default '',
        `address` varchar(255) NOT NULL default '',
        `city` varchar(255) NOT NULL default '',
        `country` varchar(255) NOT NULL default '',
        `zipcode` varchar(25) NOT NULL default '',
        `state` varchar(255) NOT NULL default '',
        `show_popup` smallint(5) unsigned NULL default 0,
        `created_time` datetime NOT NULL default '0000-00-00 00:00:00',
        PRIMARY KEY (`notice_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
          
    CREATE TABLE {$installer->getTable('simiconnector_notice_history')} (
        `history_id` int(11) unsigned NOT NULL auto_increment,
        `notice_title` varchar(255) NULL default '',    
        `notice_url` varchar(255) NULL default '',    
        `notice_content` text NULL default '',    
        `notice_sanbox` tinyint(1) NULL default '0',
        `website_id` int (11),
        `device_id` int (11),
        `type` smallint(5) unsigned,
        `category_id` int(10) unsigned  NOT NULL,
        `product_id` int(10) unsigned  NOT NULL,
        `image_url` varchar(255) NOT NULL default '',
        `location` varchar(255) NOT NULL default '',
        `distance` varchar(255) NOT NULL default '',
        `address` varchar(255) NOT NULL default '',
        `city` varchar(255) NOT NULL default '',
        `country` varchar(255) NOT NULL default '',
        `zipcode` varchar(25) NOT NULL default '',
        `state` varchar(255) NOT NULL default '',
        `show_popup` smallint(5) unsigned,
        `created_time` datetime NOT NULL default '0000-00-00 00:00:00',
        `notice_type` smallint(5) unsigned,
        `status` smallint(5) unsigned,
        `devices_pushed` text NULL default '',
        `notice_id` int NULL,
    PRIMARY KEY (`history_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
    

    CREATE TABLE {$installer->getTable('simiconnector_simibarcode')} (
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

    CREATE TABLE {$installer->getTable('simiconnector_videos')} (
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
    
    public function updateDB2Action(){
         $setup = new Mage_Core_Model_Resource_Setup();
        $installer = $setup;
        $installer->startSetup();
        $installer->getConnection()->addColumn($installer->getTable('simiconnector_banner'), 'banner_name_tablet', 'varchar(255)');
        $installer->endSetup();
        echo "success";
        
      
    }

}
