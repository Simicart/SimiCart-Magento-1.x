<?php

$installer = $this;
$installer->startSetup();
if (!$installer->tableExists('simiconnector/customermap')) {
    $installer->run("
        CREATE TABLE {$installer->getTable('simiconnector/customermap')} (
            `id` int(11) unsigned NOT NULL auto_increment,
            `customer_id` int(11) NULL default 0,
            `social_user_id` VARCHAR(255) NULL DEFAULT  '',
            `provider_id` VARCHAR(255) NULL DEFAULT  '',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
}
$installer->run("
        DROP TABLE IF EXISTS {$installer->getTable('simiconnector/customertoken')};
        CREATE TABLE {$installer->getTable('simiconnector/customertoken')} (
            `id` int(11) unsigned NOT NULL auto_increment,
            `customer_id` int(11) NULL default 0,
            `token` VARCHAR(255) NULL DEFAULT  '',
            `created_time` datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();
