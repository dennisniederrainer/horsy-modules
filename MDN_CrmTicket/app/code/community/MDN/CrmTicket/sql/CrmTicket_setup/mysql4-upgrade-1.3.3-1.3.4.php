<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('crm_ticket_message'), 'ctm_ip_address', 'VARCHAR(20) NULL');

$installer->endSetup();
