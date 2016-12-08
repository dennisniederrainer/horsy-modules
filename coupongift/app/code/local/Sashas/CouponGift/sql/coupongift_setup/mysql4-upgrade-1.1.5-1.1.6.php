<?php
/**
 * @author		Sashas
 * @category    Sashas
 * @package     Sashas_CouponGift
 * @copyright   Copyright (c) 2016 Sashas IT Support Inc. (http://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

$installer = $this;

$installer->startSetup();
$installer->getConnection()->addColumn(
		$installer->getTable('sales/quote_item'),
		'coupongift_rule_id',
		array(
				'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
				'nullable'  => false,
				'default'	=>0,
				'comment'   => 'Coupon Gift Rule'
		)
		);
$installer->endSetup();