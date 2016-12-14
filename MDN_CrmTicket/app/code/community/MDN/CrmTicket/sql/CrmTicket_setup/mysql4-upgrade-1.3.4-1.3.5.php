<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('crm_ticket_category'), 'ctc_store_id', 'int(11) NOT NULL');

$installer->getConnection()->dropColumn($installer->getTable('crm_ticket_category'), 'ctc_mail_enable');
$installer->getConnection()->dropColumn($installer->getTable('crm_ticket_category'), 'ctc_mail_server');
$installer->getConnection()->dropColumn($installer->getTable('crm_ticket_category'), 'ctc_mail_login');
$installer->getConnection()->dropColumn($installer->getTable('crm_ticket_category'), 'ctc_mail_password');
$installer->getConnection()->dropColumn($installer->getTable('crm_ticket_category'), 'ctc_category_type');

$installer->getConnection()->dropColumn($installer->getTable('crm_ticket_mail'), 'ctm_from_name');



$installer->endSetup();
