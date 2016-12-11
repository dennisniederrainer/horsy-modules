<?php

class Horsebrands_ProductReturn_Block_Adminhtml_Sales_Creditmemo_Grid extends Mage_Adminhtml_Block_Sales_Creditmemo_Grid {

	protected function _prepareCollection() {
      $collection = Mage::getResourceModel($this->_getCollectionClass());
			$resource = Mage::getSingleton('core/resource');

      $collection->join(
	        array('payment' => $resource->getTableName('order_payment')),
	        'main_table.order_id=payment.parent_id',
	        array('payment_method' => 'payment.method')
	    );
      $this->setCollection($collection);
      return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
  }

	protected function _prepareColumns()
    {
        $this->addColumnAfter('payment_method', array(
            'header'=> Mage::helper('sales')->__('Payment Method'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'payment_method',
						'filter_index' => 'payment.method'
        ), 'billing_name');

        return parent::_prepareColumns();
    }

	protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        $this->getMassactionBlock()->addItem('refund_creditmemos', array(
             'label'=> Mage::helper('sales')->__('Auswahl erstatten'),
             'url'  => $this->getUrl('*/*/refundCreditmemoMass'),
        ));

				$this->getMassactionBlock()->addItem('refund_creditmemos_wo_email', array(
             'label'=> Mage::helper('sales')->__('Auswahl erstatten - OHNE EMAIL'),
             'url'  => $this->getUrl('*/*/refundCreditmemoMassWithoutEmail'),
        ));

        return $this;
    }

}
