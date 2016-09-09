<?php

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = Mage::getModel('customer/entity_setup', 'core_setup');

$installer->startSetup();

$installer->updateAttribute(
    'customer_address',
    'telephone',
    'is_required',
    0
);

$installer->updateAttribute(
    'customer_address',
    'region',
    'is_required',
    0
);

$installer->updateAttribute(
    'customer_address',
    'region_id',
    'is_required',
    0
);


$installer->getConnection()
          ->addColumn($this->getTable('purchase_supplier'),'sup_logo', array(
              'type'      => 'VARCHAR(255)',
              'comment'   => 'cell holds the manufacturer logos filename'
              ));

$installer->endSetup();

?>
