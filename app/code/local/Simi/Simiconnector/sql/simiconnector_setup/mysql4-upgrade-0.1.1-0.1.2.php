<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('simiconnector_device'), 'count_purchase', 'int(11) ');

$installer->endSetup();
