<?php
/**
 * User: Vitali Fehler
 * Date: 27.06.13
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'newsletter/type'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('newsletteradvanced/type'))
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Type Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
    ), 'Store Id')
    ->addColumn('list_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'default'   => '0',
    ), 'List Id')
    ->addColumn('type_name', Varien_Db_Ddl_Table::TYPE_TEXT, 500, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Type Name')
    ->addColumn('type_frequency', Varien_Db_Ddl_Table::TYPE_TEXT, 500, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Type Frequency')
    ->addColumn('type_description', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Type Description')
    ->addIndex($installer->getIdxName('newsletteradvanced/type', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('newsletteradvanced/type', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);

/*
 * Create table 'newsletter/type'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('newsletteradvanced/typesubscriber'))
    ->addColumn('typesubscriber_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary' => true,
    ), 'Type Subscriber Id')
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Type Id')
    ->addColumn('subscriber_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Subscriber Id')
    ->addIndex($installer->getIdxName('newsletteradvanced/typesubscriber', array('type_id')),
        array('type_id'))
    ->addIndex($installer->getIdxName('newsletteradvanced/typesubscriber', array('subscriber_id')),
        array('subscriber_id'))
    ->addForeignKey($installer->getFkName('newsletteradvanced/typesubscriber', 'type_id', 'newsletteradvanced/type', 'type_id'),
        'type_id', $installer->getTable('newsletteradvanced/type'), 'type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('newsletteradvanced/typesubscriber', 'subscriber_id', 'newsletter/subscriber', 'subscriber_id'),
        'subscriber_id', $installer->getTable('newsletter/subscriber'), 'subscriber_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);

$installer->endSetup();
?>