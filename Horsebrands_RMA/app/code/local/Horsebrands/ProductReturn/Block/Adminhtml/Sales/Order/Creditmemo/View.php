<?php

class Horsebrands_ProductReturn_Block_Adminhtml_Sales_Order_Creditmemo_View extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_View {

    public function __construct() {
        parent::__construct();

        $this->addButton('refund_offline', array(
            'label'     => Mage::helper('sales')->__('Refund (offline)'),
            'class'     => 'save',
            'onclick'   => 'setLocation(\''.$this->getRefundOfflineUrl().'\')'
            )
        );
    }

    public function getRefundOfflineUrl() {
      return Mage::helper('adminhtml')->getUrl('adminhtml/sales_creditmemo/refundCreditmemo', array('creditmemo_id'=>$this->getCreditmemo()->getId()));
    }
}
