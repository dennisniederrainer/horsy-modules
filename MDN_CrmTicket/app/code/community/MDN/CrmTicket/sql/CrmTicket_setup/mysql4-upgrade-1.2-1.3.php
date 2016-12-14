<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('crm_default_reply'), 'cdr_must_update_status','TINYINT NOT NULL DEFAULT 1');

$installer->getConnection()->addColumn($installer->getTable('crm_ticket_category'), 'ctc_name_translation_by_store', 'VARCHAR(255) NOT NULL');


try{
    
    $installer->getConnection()->dropColumn($installer->getTable('crm_ticket_mail'), 'ctm_content');

} catch (Exception $ex) {
//crash son some upgrades
}

$installer->endSetup();
