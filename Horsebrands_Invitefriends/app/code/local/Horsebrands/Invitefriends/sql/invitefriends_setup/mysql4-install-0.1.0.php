<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('invitefriends/invites'))
    ->addColumn(
      'invite_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
      array(
          'identity' => true,
          'unsigned' => true,
          'nullable' => false,
          'primary'  => true,
      ), 'Unique identifier'
    )
    ->addColumn(
      'customer_id',Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'Blog title'
    )
    ->addColumn(
      'invitee_email', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(), 'Invitee Email Address'
    )
    ->addColumn(
      'status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'Invitation Status'
    )
    ->addColumn(
      'invitee_coupon', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(), 'Invitee Coupon Code'
    )
    ->addColumn(
      'customer_coupon', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(), 'Customer Coupon Code'
    )
    ->addColumn(
      'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
      array(
        'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT
      ), "Created At Timestamp")
    ->addColumn(
      'updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
      array(
        'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT
      ), "Updated At Timestamp");

if (!$installer->getConnection()->isTableExists($table->getName())) {
  $installer->getConnection()->createTable($table);
}

$installer->endSetup();

?>
