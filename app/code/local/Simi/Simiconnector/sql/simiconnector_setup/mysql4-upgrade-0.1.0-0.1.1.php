<?php

$installer = $this;
$installer->startSetup();


$installer->getConnection()->addColumn($installer->getTable('simiconnector_notice'), 'click', 'int(11) NOT NULL default "0"');
$installer->getConnection()->addColumn($installer->getTable('simiconnector_notice'), 'time_to_send', 'datetime  NULL default "0000-00-00 00:00:00"');
$installer->getConnection()->addColumn($installer->getTable('simiconnector_notice'), 'status_send', 'smallint(6) NOT NULL  default "0"');
$installer->getConnection()->addColumn($installer->getTable('simiconnector_notice_history'), 'click', 'int(11) NOT NULL default "0"');

$installer->endSetup();
