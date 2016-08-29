<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
          ->addColumn($this->getTable('purchase_supplier'),'sup_logo', array(
              'type'      => 'VARCHAR(255)',
              'comment'   => 'cell holds the manufacturer logos filename'
              ));

$installer->endSetup();

?>
