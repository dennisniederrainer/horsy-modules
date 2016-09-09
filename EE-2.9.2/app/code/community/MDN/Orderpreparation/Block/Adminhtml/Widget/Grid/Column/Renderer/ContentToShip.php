<?php

/*
 * Display content to ship in selected orders
 */

class MDN_Orderpreparation_Block_Adminhtml_Widget_Grid_Column_Renderer_ContentToShip extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    private $_itemsToShip = null;

    /**
     *
     * @param Varien_Object $row
     * @return string 
     */
    public function render(Varien_Object $row) {
        $retour = "";

        $order = $row;
        $OrderToPrepare = mage::getModel('Orderpreparation/ordertoprepare')->load($order->getId(), 'order_id');

        //Build string with content to ship
        $this->_itemsToShip = mage::getModel('Orderpreparation/ordertoprepare')->GetItemsToShip($order->getId());
        foreach ($this->_itemsToShip as $item) {
            $orderItem = $item->getSalesOrderItem();
            $productId = $orderItem->getproduct_id();
            $name = $orderItem->getName();
            $name .= mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getDescription($productId);
            if (($orderItem->getProductType() == 'configurable') || ($orderItem->getProductType() == 'bundle'))
                $retour .= '<i>' . $item->getqty() . 'x ' . $name . '</i>';
            else {
                if ($item->getSalesOrderItem()->getparent_item_id())
                    $retour .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $item->getqty() . 'x ' . $name;
                else
                    $retour .= $item->getqty() . 'x ' . $name;
            }

            //add remove button (if allowed)
            if ($this->canRemoveItem($order, $orderItem, $OrderToPrepare))
            {
                $url = $this->getUrl('OrderPreparation/OrderPreparation/removeItem', array('order_item_id' => $orderItem->getId()));
                $retour .= '&nbsp;<a href="'.$url.'"><img src="' . $this->getSkinUrl('images/cancel_icon.gif') . '"></a>';
            }

            $retour .= '<br>';
        }

        return $retour;
    }

    /**
     * Return true if operator can manually remove item from preparation
     */
    protected function canRemoveItem($order, $orderItem, $orderToPrepare) {
        //if order is shipped, return false
        if ($orderToPrepare->getshipment_id())
            return false;

        //if item is alone in order, return false
        if ($this->getNoParentCount() == 1)
            return false;

        //if item has parent, return false
        if ($orderItem->getparent_item_id())
            return false;

        return true;
    }

    /**
     * Return number of products to prepare without parents
     */
    protected function getNoParentCount() {
        $count = 0;
        foreach ($this->_itemsToShip as $item) {
            if (!$item->getSalesOrderItem()->getparent_item_id())
                $count++;
        }

        return $count;
    }

}