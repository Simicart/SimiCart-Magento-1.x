<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('simiconnector_cms'), 'cms_script', 'text NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('simiconnector_cms'), 'cms_url', 'varchar(255) NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('simiconnector_cms'), 'cms_meta_title', 'varchar(255) NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('simiconnector_cms'), 'cms_meta_desc', 'varchar(255) NULL default ""');

$installer->endSetup();
