<?php

class MDN_OrderPreparation_Block_Packing_Index extends Mage_Core_Block_Template {

    public function getOrderInformationUrl() {
        return Mage::helper('adminhtml')->getUrl('OrderPreparation/Packing/OrderInformation');
    }

    public function getCheckedImageUrl() {
        return $this->getSkinUrl('images/scanner/ok.png');
    }

    public function getCommitPackingUrl() {
        return $this->getUrl('OrderPreparation/Packing/Commit');
    }

    public function getTranslateJson() {
        $translations = array(
            'Scan order to Pack' => $this->__('Scan order to Pack'),
            'Please scan products' => $this->__('Please scan products'),
            'An error occured' => $this->__('An error occured'),
            'Unknown barcode ' => $this->__('Unknown barcode '),
            'Product quantity already scanned !' => $this->__('Product quantity already scanned !'),
            ' scanned' => $this->__(' scanned'),
            ' are missing !' => $this->__(' are missing !'),
            );
        return Mage::helper('core')->jsonEncode($translations);
    }
    
    /**
     * 
     * @return type
     */
    public function getOrderToConfirm()
    {
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        if ($orderId)
        {
            $orderToPrepare = mage::getModel('Orderpreparation/ordertoprepare')->load($orderId, 'order_id');
            return $orderToPrepare;
        }
        else
            return null;
    }
    
    /**
     * Return url to download invoice for packed order
     */
    public function getDownloadInvoiceUrl()
    {
        return $this->getUrl('*/*/printInvoice', array('invoice_increment_id' => $this->getOrderToConfirm()->getinvoice_id()));
    }

    
    /**
     * Return url to download packing slip for packed order
     */
    public function getDownloadPackingSlipUrl()
    {
        return $this->getUrl('*/*/printShipment', array('shipment_increment_id' => $this->getOrderToConfirm()->getshipment_id()));
    }
    
    /**
     * Return url to download shipping software file for the current order
     */
    public function getDownloadShippingLabelFileUrl()
    {
        return $this->getUrl('*/*/downloadShippingLabelFile', array('order_id' => $this->getOrderToConfirm()->getorder_id()));
    }
    
}
