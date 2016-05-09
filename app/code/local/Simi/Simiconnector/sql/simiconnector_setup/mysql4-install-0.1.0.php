<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$installer->getTable('simicategory')};
    DROP TABLE IF EXISTS {$installer->getTable('connector_banner')};

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

");

$installer->endSetup();
