<?php

$installer = $this;
$installer->startSetup();

//migration Status
$installer->run("UPDATE {$this->getTable('crm_ticket')} SET ct_status = '".MDN_CrmTicket_Model_Ticket::STATUS_CLOSED."' WHERE ct_status = '".MDN_CrmTicket_Model_Ticket::STATUS_RESOLVED."' ");
$installer->run("UPDATE {$this->getTable('crm_ticket')} SET ct_status = '".MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_ADMIN."' WHERE ct_status = '".MDN_CrmTicket_Model_Ticket::STATUS_NEW."' ");

$installer->run("UPDATE {$this->getTable('crm_email_router_rule')} SET cerr_status = '".MDN_CrmTicket_Model_Ticket::STATUS_CLOSED."' WHERE cerr_status = '".MDN_CrmTicket_Model_Ticket::STATUS_RESOLVED."' ");
$installer->run("UPDATE {$this->getTable('crm_email_router_rule')} SET cerr_status = '".MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_ADMIN."' WHERE cerr_status = '".MDN_CrmTicket_Model_Ticket::STATUS_NEW."' ");

$installer->run("UPDATE {$this->getTable('core_config_data')} SET  value = '".MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_ADMIN."' WHERE  path = 'crmticket/pop/default_status_during_import' ");
$installer->run("UPDATE {$this->getTable('core_config_data')} SET  value = '".MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_ADMIN."' WHERE  path = 'crmticket/pop/ticket_grid_default_status' ");

$installer->endSetup();
