<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('simiconnector_transactions'), 'platform', 'int(11) NOT NULL default "0"');

$installer->endSetup();
