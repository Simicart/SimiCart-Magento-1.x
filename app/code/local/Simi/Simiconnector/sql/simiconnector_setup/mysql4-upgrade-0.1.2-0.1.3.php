<?php

$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('simiconnector_notice'), 'server_time_to_send', 'datetime  NULL default "0000-00-00 00:00:00"');

$installer->endSetup();
