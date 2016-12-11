<?php

$installer = $this;
$installer->startSetup();



$installer->getConnection()->addIndex($installer->getTable('crm_ticket'),$installer->getIdxName('crm_ticket', array('ct_status')),array('ct_status'));
$installer->getConnection()->addIndex($installer->getTable('crm_ticket'),$installer->getIdxName('crm_ticket', array('ct_customer_id')),array('ct_customer_id'));
$installer->getConnection()->addIndex($installer->getTable('crm_ticket_message'),$installer->getIdxName('crm_ticket_message', array('ctm_ticket_id')),array('ctm_ticket_id'));

$installer->getConnection()->addColumn($installer->getTable('crm_email_router_rule'), 'cerr_tag_id', 'int(11) NOT NULL');

$installer->endSetup();
