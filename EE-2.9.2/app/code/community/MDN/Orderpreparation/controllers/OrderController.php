<?php

class MDN_Orderpreparation_OrderController extends Mage_Adminhtml_Controller_Action {
    
    public function DispatchAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        
        $result = Mage::helper('Orderpreparation/Dispatcher')->DispatchOrder($order);
                
        //confirm & redirect
        echo '<p>Dispatch order #'.$order->getincrement_id().' ('.$orderId.')</p>';
        die($result.'<--');
        
    }
    
}
