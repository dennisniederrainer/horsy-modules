<?php
/**
 * @author		Sashas
 * @category    Sashas
 * @package     Sashas_CouponGift
 * @copyright   Copyright (c) 2016 Sashas IT Support Inc. (http://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

class Sashas_CouponGift_Model_Observer {
	protected static $_singletonFlag = false;
	const COUPON_GIFT_CODE = 'coupon_gift';

	public function SalesRulePrepareForm(Varien_Event_Observer $observer) {
		$form = $observer->getForm ();

		$field = $form->getElement ( 'simple_action' );
		$options = $field->getValues ();

		$options [] = array ('value' => self::COUPON_GIFT_CODE, 'label' => 'Add Gift Product' );

		$field->setValues ( $options );

		$after_element_js = "
		<script type=\"text/javascript\" >
		document.getElementById('rule_simple_action').addEventListener('change', function(){couponGiftFields();}, false);
		function couponGiftFields () {
			if ($('rule_simple_action').value=='" . self::COUPON_GIFT_CODE . "') {
				$('rule_discount_amount').value=0;
				if ($$('#rule_action_fieldset tr')[0]!=undefined) {
					$$('#rule_action_fieldset tr').each(function(tr_el) {
						if  ($(tr_el).down('#rule_gift_product_sku')!=undefined || $(tr_el).down('#rule_gift_product_force_price')!=undefined  )
							$(tr_el).show();
						else
							$(tr_el).hide();

					if ($(tr_el).down('#rule_simple_action')!=undefined || $(tr_el).down('#rule_discount_amount')!=undefined  )
						$(tr_el).show();
					});
				}
			} else {
				if ($$('#rule_action_fieldset tr')[0]!=undefined) {
					$$('#rule_action_fieldset tr').each(function(tr_el) {
						if  ($(tr_el).down('#rule_gift_product_sku')!=undefined || $(tr_el).down('#rule_gift_product_force_price')!=undefined  )
							$(tr_el).hide();
						else
							$(tr_el).show();

					if ($(tr_el).down('#rule_simple_action')!=undefined || $(tr_el).down('#rule_discount_amount')!=undefined  )
						$(tr_el).show();
					});
				}
			}
		}
		document.observe('dom:loaded', function() {  couponGiftFields(); });
		</script>";
		$field->setAfterElementHtml ( $after_element_js );

		$fieldset = $form->getElement ( 'action_fieldset' );

		$fieldset->addField ( 'gift_product_sku', 'text', array ('name' => 'gift_product_sku', 'label' => Mage::helper ( 'coupongift' )->__ ( 'Gift Product SKU' ) ) );

		$fieldset->addField ( 'gift_product_force_price', 'select', array ('name' => 'gift_product_force_price', 'label' => Mage::helper ( 'cms' )->__ ( 'Set gift product price 0' ), 'options' => array (1 => "Yes", 0 => "No" ) ) );

		return $this;
	}

	public function SalesRuleGiftValidator(Varien_Event_Observer $observer) {

		$rule = $observer->getRule ();
		if ($rule->getSimpleAction () != self::COUPON_GIFT_CODE) {
			return $this;
		}

		$force_price = $rule->getGiftProductForcePrice ();
		$gift_product_sku = $rule->getGiftProductSku ();
		$quoteObj = Mage::getModel ( 'checkout/cart' )->getQuote ();
		$product_id = Mage::getModel ( 'catalog/product' )->getIdBySku ( $gift_product_sku );
		$cart_obj = Mage::getModel ( 'checkout/cart' );
		$delete_gift_product = 0;

		if (! $product_id) {
			Mage::getSingleton ( 'checkout/session' )->addError(Mage::helper ( 'coupongift' )->__ ( 'Gift Product SKU "%s" Not Found.', Mage::helper ( 'core' )->htmlEscape ( $gift_product_sku ) ) );
			return $this;
		}

		$ruleItemAdded=Mage::registry('coupongift_rule_applied_'.$rule->getRuleId());
		$gift_quote_item = null;
		foreach ( $cart_obj->getItems () as $quote_item ) {
			if ($quote_item->getCoupongiftRuleId () == $rule->getRuleId()) {

				if (!$ruleItemAdded){
					Mage::register('coupongift_rule_applied_'.$rule->getRuleId(),1);
					$ruleItemAdded=1;
				}
				$gift_quote_item = $quote_item;
				 break;
			}
		}
		if ($gift_quote_item) {
			$gift_quote_item->setQty (1);
		}

		/*
		 * Check if original product was deleted
		 */
		if (count($cart_obj->getItems()) < 2 && $gift_quote_item instanceof Mage_Sales_Model_Quote_Item)
			$delete_gift_product = 1;

		/*If cancelled by other rule*/
		if (Mage::registry('coupongift_stop_rule_processing')) {
			$delete_gift_product = 1;
		}

		if ($ruleItemAdded && ! $delete_gift_product)
			return $this;

		$_product = Mage::getModel ( 'catalog/product' )->load ( $product_id );
		/*
		 * Check if original product was deleted
		 */
		if ($delete_gift_product) {
			$quoteObj->removeItem ( $gift_quote_item->getId () );
			$gift_quote_item->isDeleted ( true );
			/*$quoteObj->save ();*/
			if (Mage::registry('coupongift_rule_applied_'.$rule->getRuleId())) {
				Mage::unregister('coupongift_rule_applied_'.$rule->getRuleId());
			}
			return $this;
		}

		$quoteItem = Mage::getModel ( 'sales/quote_item' )->setProduct ( $_product );

		 /*Force price*/
		if ($force_price)
			$quoteItem->setOriginalCustomPrice ( 0 );
		else
			$quoteItem->setOriginalCustomPrice ();
		/*Force price*/

		$quoteItem->setQty(1);
		$quoteItem->setIsCoupongift(1);
		$quoteItem->setCoupongiftRuleId($rule->getRuleId());
		$quoteObj->addItem($quoteItem );
		$quoteObj->save();
		if (!Mage::registry('coupongift_rule_applied_'.$rule->getRuleId()))
			Mage::register('coupongift_rule_applied_'.$rule->getRuleId(),1);

		if ($rule->getStopRulesProcessing() && !Mage::registry('coupongift_stop_rule_processing')) {
			Mage::register('coupongift_stop_rule_processing', 1);
		}
		return $this;
	}

	public function RemoveCoupon(Varien_Event_Observer $observer) {

		/*Remove Action*/
		if (Mage::app ()->getRequest ()->getParam ( 'remove' ) != 1)
			return $this;

		$quote = $observer->getQuote ();
		$quote_id = $quote->getEntityId ();
		$dbQuote=Mage::getModel ( 'sales/quote' )->load ( $quote_id) ;
		$appliedCouponIds =$dbQuote->getAppliedRuleIds ();

		if (!$appliedCouponIds)
			return $this;

		$appliedCouponIdsArr=explode(',', $appliedCouponIds);
		$couponCode=$dbQuote->getCouponCode();

		foreach ( $appliedCouponIdsArr as $apr ) {
			if (!$apr)
				continue;
			$tmpRule = Mage::getModel ( 'salesrule/rule' )->load ( $apr );

			if ($tmpRule->getSimpleAction()!= 'coupon_gift')
				continue;

			if ($couponCode==$tmpRule->getCouponCode()) {
				$cartObj=Mage::getModel('checkout/cart');
				foreach ($cartObj->getItems() as $quoteItem ) {
					if ($quoteItem instanceof Mage_Sales_Model_Quote_Item && $quoteItem->getCoupongiftRuleId() == $tmpRule->getRuleId()) {
						$quoteItem->isDeleted(true);
						if (Mage::registry('coupongift_rule_applied_'.$tmpRule->getRuleId()))
							Mage::unregister('coupongift_rule_applied_'.$tmpRule->getRuleId());
					}
				}
				break;
			}
		}

		return $this;
	}

	public function UpdateCartItem(Varien_Event_Observer $observer) {

		$new_info = $observer->getInfo ();
		$cart = $observer->getCart ();

		$quoteObj = Mage::getModel ( 'checkout/cart' )->getQuote ();
		$quoteObj->setTotalsCollectedFlag ( false )->collectTotals ()->save ();

		$appliedCouponIds = Mage::getModel ( 'sales/quote' )->load ( $quoteObj->getEntityId () )->getAppliedRuleIds ();
		$appliedCouponIdsArr=explode(',', $appliedCouponIds);

		/*Remove if not rules set */
		if (count($appliedCouponIdsArr)==1 && $appliedCouponIdsArr[0]=="") {
			foreach ( $observer->getCart ()->getItems () as $quoteItem ) {
				if ($quoteItem->getCoupongiftRuleId()){
					if (Mage::registry('coupongift_rule_applied_'.$quoteItem->getCoupongiftRuleId()))
						Mage::unregister('coupongift_rule_applied_'.$quoteItem->getCoupongiftRuleId());
					$quoteObj->removeItem ( $quoteItem->getId () );
				}
			}
			$quoteObj->setTotalsCollectedFlag ( false )->collectTotals ()->save ();
			return $this;
		}


		/*  Re-add products*/
		foreach ( $appliedCouponIdsArr as $apr ) {
			$tmpRule = Mage::getModel ( 'salesrule/rule' )->load ( $apr );
			$ruleItemAdded=Mage::registry('coupongift_rule_applied_'.$tmpRule->getRuleId());

			if ($tmpRule->getSimpleAction()!= 'coupon_gift' || $ruleItemAdded)
				continue;

			foreach ( $observer->getCart ()->getItems () as $quoteItem ) {
				if ($quoteItem->getCoupongiftRuleId () == $tmpRule->getRuleId()) {
					unset ($new_info[$quoteItem->getId()]);
					$quoteItemBack = clone $quoteItem;
					$quoteObj->removeItem ( $quoteItem->getId () );
					$quoteItemBack->setQty(1);
					$quoteItemBack->setIsCoupongift(1);
					$quoteItem->setCoupongiftRuleId($tmpRule->getRuleId());
					$quoteObj->addItem ( $quoteItemBack );
					$quoteObj->setTotalsCollectedFlag ( false )->collectTotals ()->save ();
					if (!Mage::registry('coupongift_rule_applied_'.$tmpRule->getRuleId()))
						Mage::register('coupongift_rule_applied_'.$tmpRule->getRuleId(),1);
					break;
				}
			}
		}
		$quoteObj->setTotalsCollectedFlag ( false )->collectTotals ()->save ();


		return $this;
	}

	public function RemovefromCart(Varien_Event_Observer $observer) {


		$removedQuoteItem = $observer->getQuoteItem ();
		$removedPid = $removedQuoteItem->getProductId ();

		$cart = Mage::getModel ( 'checkout/cart' );
		$quoteObj = Mage::getModel ( 'checkout/cart' )->getQuote();

		/*Remove if another gift rule applied*/
		if (Mage::registry('coupongift_stop_rule_processing') && $removedQuoteItem->getIsCoupongift()) {
			$removedQuoteItem->isDeleted(true );
			$quoteObj->removeItem ($removedQuoteItem->getId())->save ();
			return $this;
		}
		$quoteObj->setTotalsCollectedFlag (false)->collectTotals ()->save();
		$quoteObj = Mage::getModel ( 'checkout/cart' )->getQuote ();

		$appliedCouponIds = Mage::getModel ( 'sales/quote' )->load ( $quoteObj->getEntityId () )->getAppliedRuleIds ();
		$appliedCouponIdsArr=explode(',', $appliedCouponIds);

		/*Remove if not rules set */
		if (count($appliedCouponIdsArr)==1 && $appliedCouponIdsArr[0]=="") {
			foreach ($quoteObj->getAllItems() as $quoteItem ) {
				if ($quoteItem->getCoupongiftRuleId()){
					if (Mage::registry('coupongift_rule_applied_'.$quoteItem->getCoupongiftRuleId()))
						Mage::unregister('coupongift_rule_applied_'.$quoteItem->getCoupongiftRuleId());

					$quoteObj->removeItem ( $quoteItem->getId () );
					/*	$quoteObj->save ();*/
				}
			}
			$quoteObj->setTotalsCollectedFlag ( false )->collectTotals ()->save ();
			return $this;
		}
		/* @todo check case if exist then add*/
		/*remove if several rules were applied*/
		foreach ($quoteObj->getAllItems() as $quoteItem ) {
			if ($quoteItem->getCoupongiftRuleId() && !in_array($quoteItem->getCoupongiftRuleId(), $appliedCouponIdsArr) ) {
				if (Mage::registry('coupongift_rule_applied_'.$quoteItem->getCoupongiftRuleId()))
					Mage::unregister('coupongift_rule_applied_'.$quoteItem->getCoupongiftRuleId());
				$quoteObj->removeItem($quoteItem);
			}
		}
		$quoteObj->setTotalsCollectedFlag ( false )->collectTotals ()->save ();
		return $this;

	}

	/**
	 * @param Varien_Event_Observer $observer
	 * @return Sashas_CouponGift_Model_Observer
	 */
	public function salesQuoteSaveAfter(Varien_Event_Observer $observer) {

		$quote = $observer->getEvent()->getQuote();
		$aplliedRules=explode(',',$quote->getAppliedRuleIds());

		if (count($aplliedRules)==1 && $aplliedRules[0]=="")
			return $this;

		foreach ($quote->getAllVisibleItems() as $quoteItem) {
			if ($quoteItem->getCoupongiftRuleId() && !in_array($quoteItem->getCoupongiftRuleId(), $aplliedRules) ) {
				if (Mage::registry('coupongift_rule_applied_'.$quoteItem->getCoupongiftRuleId()))
					Mage::unregister('coupongift_rule_applied_'.$quoteItem->getCoupongiftRuleId());
				$quote->deleteItem($quoteItem);
			}
		}
		return $this;
	}

}
