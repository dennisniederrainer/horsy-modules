<?php

class MDN_SalesOrderPlanning_LateOrdersController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
}