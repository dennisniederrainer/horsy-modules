<?php

class MDN_AdvancedStock_Block_Serial_Widget_Grid_Column_Renderer_PurchaseOrder extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	if ($row->getpps_purchaseorder_id())
    	{
    		$purchaseOrderId = $row->getpps_purchaseorder_id();
    		$purchaseOrder = mage::getModel('Purchase/Order')->load($purchaseOrderId);
	    	$url = $this->getUrl('Purchase/Orders/Edit', array('po_num' => $purchaseOrderId));
	    	$retour = '<a href="'.$url.'">'.$purchaseOrder->getpo_order_id().'</a>';
    	}
    	else 
    		$retour = '';
    		
		return $retour;
    }
    
}
