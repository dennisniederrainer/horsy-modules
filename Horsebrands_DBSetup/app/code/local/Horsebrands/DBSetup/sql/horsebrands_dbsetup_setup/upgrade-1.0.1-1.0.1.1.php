<?php

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = Mage::getModel('customer/entity_setup', 'core_setup');
$installer->startSetup();

$installer->getConnection()
      ->addColumn(
          $this->getTable('purchase_supplier'),
          'sup_logo',
          Varien_Db_Ddl_Table::TYPE_TEXT);

$installer->endSetup();

?>
