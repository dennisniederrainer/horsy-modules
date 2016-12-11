<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('crm_ticket_mail'), 'ctm_isfilecontent', 'TINYINT NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($installer->getTable('crm_email_account'), 'cea_email', 'VARCHAR(250) NOT NULL');

$installer->endSetup();
