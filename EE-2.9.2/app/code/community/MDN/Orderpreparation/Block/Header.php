<?php

/**
 * Block pour l'index de la page de pr�paration de commandes
 *
 */
class MDN_OrderPreparation_Block_Header extends Mage_Core_Block_Template {

    protected function _construct() {
        parent::_construct();

        $this->setTemplate('Orderpreparation/Header.phtml');
    }

    /**
     * return button list
     *
     */
    public function getButtons() {

        $retour = array();

        //select orders
        $item = array();
        $item['position'] = count($retour) + 1;
        $item['onclick'] = "document.location.href='" . Mage::helper('adminhtml')->getUrl('OrderPreparation/OrderPreparation') . "'";
        $item['caption'] = $this->__('Select orders');
        $item['base_url'] = 'OrderPreparation/OrderPreparation/index';
        $retour['select_orders'] = $item;

        //print (or download) picking list
        if (Mage::getStoreConfig('orderpreparation/order_preparation_step/display_picking_list_button')) {
            if (mage::getStoreConfig('orderpreparation/order_preparation_step/print_method') == 'download') {
                $item = array();
                $item['position'] = count($retour) + 1;
                $item['onclick'] = "document.location.href='" . Mage::helper('adminhtml')->getUrl('OrderPreparation/OnePagePreparation/DownloadPickingList') . "'";
                $item['caption'] = $this->__('Picking list');
                $retour['download_picking_list'] = $item;
            } else {
                $item = array();
                $item['position'] = count($retour) + 1;
                $confirmMsg = $this->__('Picking list sent to printer');
                $item['onclick'] = "ajaxCall('" . Mage::helper('adminhtml')->getUrl('OrderPreparation/OnePagePreparation/PrintPickingList') . "', '" . $confirmMsg . "')";
                $item['caption'] = $this->__('Picking list');
                $retour['print_picking_list'] = $item;
            }
        }

        //Create shipments & invoices
        if (Mage::getStoreConfig('orderpreparation/order_preparation_step/display_create_shipments_button') == 1) {
            $item = array();
            $item['position'] = count($retour) + 1;
            $item['onclick'] = "document.location.href='" . Mage::helper('adminhtml')->getUrl('OrderPreparation/OrderPreparation/Commit') . "'";
            $item['caption'] = $this->__('Create shipments/invoices');
            $item['base_url'] = 'OrderPreparation/OrderPreparation/ShipmentAndInvoicesCreated';
            $retour['create_objects'] = $item;
        }

        //Download documents
        if (Mage::getStoreConfig('orderpreparation/order_preparation_step/display_download_document_button') == 1) {
            if (mage::getStoreConfig('orderpreparation/order_preparation_step/print_method') == 'download') {
                $item = array();
                $item['position'] = count($retour) + 1;
                $item['onclick'] = "document.location.href='" . Mage::helper('adminhtml')->getUrl('OrderPreparation/OrderPreparation/DownloadDocuments') . "'";
                $item['caption'] = $this->__('Download documents');
                $retour['download_documents'] = $item;
            } else {
                $item = array();
                $item['position'] = count($retour) + 1;
                $confirmMsg = $this->__('Documents sent to printer');
                $item['onclick'] = "ajaxCall('" . Mage::helper('adminhtml')->getUrl('OrderPreparation/OrderPreparation/PrintDocuments') . "', '" . $confirmMsg . "')";
                $item['caption'] = $this->__('Print documents');
                $retour['print_documents'] = $item;
            }
        }

        //Packing
        if (mage::getStoreConfig('orderpreparation/order_preparation_step/display_packing_button')) {
            $item = array();
            $item['position'] = count($retour) + 1;
            $item['onclick'] = "document.location.href='" . Mage::helper('adminhtml')->getUrl('OrderPreparation/Packing') . "'";
            $item['caption'] = $this->__('Packing');
            $item['base_url'] = 'OrderPreparation/Packing/index';
            $retour['process_orders'] = $item;
        }

        //Import trackings
        if (Mage::getStoreConfig('orderpreparation/order_preparation_step/display_shipping_label_button') == 1) {
            $item = array();
            $item['position'] = count($retour) + 1;
            $item['onclick'] = "document.location.href='" . Mage::helper('adminhtml')->getUrl('OrderPreparation/CarrierTemplate/ImportTracking') . "'";
            $item['caption'] = $this->__('Shipping label');
            $item['base_url'] = 'OrderPreparation/CarrierTemplate/ImportTracking';
            $retour['shipping_label_trackings'] = $item;
        }

        //Notify customers
        $item = array();
        $item['position'] = count($retour) + 1;
        $confirmMsg = $this->__('Customers notified');
        $item['onclick'] = "ajaxCall('" . Mage::helper('adminhtml')->getUrl('OrderPreparation/OrderPreparation/NotifyCustomers') . "', '" . $confirmMsg . "')";
        $item['caption'] = $this->__('Notify');
        $retour['notify_customers'] = $item;

        //Finish
        $item = array();
        $item['position'] = count($retour) + 1;
        $item['onclick'] = "document.location.href='" . Mage::helper('adminhtml')->getUrl('OrderPreparation/OrderPreparation/Finish') . "'";
        $item['caption'] = $this->__('Finish');
        $retour['finish'] = $item;

        return $retour;
    }

   /**
     *
     * @param <type> $item
     * @return <type>
     */
    public function isCurrentItem($item)
    {
        $request = Mage::app()->getRequest();
        $module = strtolower($request->getModuleName());
        $controller = strtolower($request->getControllerName());
        $action = strtolower($request->getActionName());
        $url = $module.'/'.$controller.'/'.$action;

        if (isset($item['base_url']))
        {

            if (strtolower($item['base_url']) == $url)
                return true;
        }

        return false;
        
    }
    
    
}