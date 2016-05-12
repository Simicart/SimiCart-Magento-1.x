<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$installer->getTable('simicategory')};
    DROP TABLE IF EXISTS {$installer->getTable('connector_banner')};
    DROP TABLE IF EXISTS {$installer->getTable('connector_cms')};
    DROP TABLE IF EXISTS {$installer->getTable('connector_device')};          
    DROP TABLE IF EXISTS {$installer->getTable('connector_notice')};
    DROP TABLE IF EXISTS {$installer->getTable('connector_notice_history')};

    CREATE TABLE {$installer->getTable('simicategory')} (
        `simicategory_id` int(11) unsigned NOT NULL auto_increment,
        `simicategory_name` varchar(255),
        `simicategory_filename` varchar(255),
        `category_id` int(8),
        `status` smallint(6) NOT NULL default '0',
        `website_id` int(6),
        PRIMARY KEY (`simicategory_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    CREATE TABLE {$installer->getTable('connector_banner')} (
        `banner_id` int(11) unsigned NOT NULL auto_increment,
        `banner_name` varchar(255) NULL, 
        `banner_url` varchar(255) NULL default '',
        `banner_title` varchar(255) NULL,
        `status` int(11) NULL,  
        `website_id` smallint(5) NULL,
        `type` smallint(5) unsigned default 3,
        `category_id` int(10) unsigned  NOT NULL,
        `product_id` int(10) unsigned  NOT NULL,        
        PRIMARY KEY (`banner_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
    
    CREATE TABLE {$installer->getTable('connector_cms')} (
        `cms_id` int(11) unsigned NOT NULL auto_increment,
        `cms_title` varchar(255) NULL, 
        `cms_image` varchar(255) NULL default '', 
        `cms_content` text NULL default '',  
        `cms_status` tinyint(4) NOT NULL default '1',
        `website_id` smallint(5) NULL,
        PRIMARY KEY (`cms_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
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
        PRIMARY KEY (`device_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
    CREATE TABLE {$installer->getTable('connector_notice')} (
        `notice_id` int(11) unsigned NOT NULL auto_increment,
        `notice_title` varchar(255) NULL default '',    
        `notice_url` varchar(255) NULL default '',    
        `notice_content` text NULL default '',    
        `notice_sanbox` tinyint(1) NULL default '0',
        `website_id` int (11),
        `device_id` int (11),
        PRIMARY KEY (`notice_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
          
    CREATE TABLE {$installer->getTable('connector_notice_history')} (
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
");
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'type', 'smallint(5) unsigned');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'category_id', 'int(10) unsigned  NOT NULL');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'product_id', 'int(10) unsigned  NOT NULL');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'image_url', 'varchar(255) NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'location', 'varchar(255) NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'distance', 'varchar(255) NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'address', 'varchar(255) NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'city', 'varchar(255) NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'country', 'varchar(255) NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'zipcode', 'varchar(25) NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'state', 'varchar(255) NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'show_popup', 'smallint(5) unsigned');
$installer->getConnection()->addColumn($installer->getTable('connector_notice'), 'created_time', 'datetime NOT NULL default "0000-00-00 00:00:00"');

$installer->endSetup();
