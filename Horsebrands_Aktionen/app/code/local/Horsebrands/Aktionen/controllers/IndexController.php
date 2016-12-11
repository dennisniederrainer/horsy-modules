<?php
class Horsebrands_Aktionen_IndexController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
    $session = Mage::getSingleton('customer/session');
    // $this->persistendLogin($session);

    if (!$session->isLoggedIn()) {
        $this->_redirect('aktionen/index/login');
        return;
    }

    $this->loadLayout();
    $this->getLayout()
            ->getBlock('aktionen');

		$this->_initLayoutMessages('customer/session');

    $this->renderLayout();
  }

	public function enablePreviewAction() {
		$secret = Mage::getStoreConfig('privatesales/general/parole');
		$key = $_GET['parole'];

		if ($key && $key == $secret) {
			Mage::getSingleton('customer/session')->setPreviewMode(true);
			Mage::getSingleton('customer/session')->addSuccess('Preview Modus erfolgreich aktiviert!');
		} else {
			Mage::getSingleton('customer/session')->addError('Die Parole stimmt nicht überein.');
		}

		$this->_redirect('aktionen');
	}

	public function loginAction() {
		$this->loadLayout()
					->_initLayoutMessages('customer/session');
					
		$this->renderLayout();
	}

	public function refreshProductsAction() {
    $catId = 1292;
    $category = Mage::getModel('catalog/category')->load($catId);

		$all_cat_ids = explode(",", $category->getAllChildren());

    $prodCollection = Mage::getResourceModel('catalog/product_collection')
			->addCategoryFilter($category)
			->addAttributeToSelect('*');

		echo $prodCollection->count() . ' Products<br/>';

    foreach ($prodCollection as $product) {
    	try
    	{
				if($product->getTypeId() == 'configurable') {
					$childProducts = Mage::getModel('catalog/product_type_configurable')
                  ->getUsedProducts(null,$product);

					foreach ($childProducts as $child) {
						$productAvailabilityStatus = mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')->load($child->getId(), 'pa_product_id');
						$productAvailabilityStatus->refresh();
					}

				}elseif($product->getTypeId() == 'simple') {
					$productAvailabilityStatus = mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')->load($product->getId(), 'pa_product_id');
					$productAvailabilityStatus->refresh();
				}
      }
    	catch (Exception $ex)
    	{
    		// Mage::getSingleton('adminhtml/session')->addSuccess($this->__('An error occured : ').$ex->getMessage());
    	}
    }
  	// $this->_redirect('SalesOrderPlanning/ProductAvailabilityStatus/Grid/');

    die('refreshed.');
  }

	public function testAction() {
  		$product = Mage::getModel('catalog/product')->load('BIE0200231','sku');
			echo $product->isSalable() . '<br/>';

			die('moin.');
  }

    protected function persistendLogin($session) {
        try {
            $persistent_login = Mage::getModel('core/cookie')->get('horse_stay_logged_in');
            if ($persistent_login && $session) {
                //persistent_login is the customer-id
                $session->loginById($persistent_login);

								if(Mage::helper('shop')->isCustomerBlocked()) {
									$session->logout();
									setcookie('horse_stay_logged_in',null,-1,'/');
									$this->_redirect('hello');
									return;
								}

                if (!$session->isLoggedIn()) {
                    // $response['html'] = Mage::helper('hello')->getAjaxErrorMessageBlock($this->__('Dein Session-Cookie ist abgelaufen. Melde dich bitte erneut an.'));
                } else {
                    Mage::log('customer with Id: ' . $session->getCustomer()->getId() . ' is logged in automatically. Time: ' . date('Y.m.d H:i:s'), null, 'auto_login.log');
                }
            }
        } catch (Exception $e) {
            //do nothing
        }
    }

    public function fireEventAction() {
        // $incrementId = 200006405;
        // $order = Mage::getModel('sales/order')->load($incrementId, 'increment_id');
				//
				// $order->cancel();
				// $order->save();
        // Mage::dispatchEvent('payment_status_changed', array('id' => $order->getIncrementId()));

        die('damage done.');
    }

    public function setOrderStatusAction() {
        // SET ORDER STATUS TO PROCESSING
        // $orderId = 200023526;
        // $order = Mage::getModel('sales/order')->load($orderId, 'increment_id');
        // $order->setState(Mage_Sales_Model_Order::STATE_CANCELED)
        //     ->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);
				//
				// $items = $order->getAllVisibleItems();
				// foreach($items as $i){
				//    $i->setQtyInvoice(0);
				// 	 $i->setQtyCanceled(1);
				//    $i->save();
				// }
				// $order->cancel();
				// $order->save();
        // die('order status set.');

//         //Update Order id
// $orderId = 200016252;


// $order = Mage::getModel('sales/order')->load($orderId, 'increment_id');

// // check if has shipments
// if(!$order->hasShipments()){
//     die('No Shipments');
// }

// //delete shipment
// $shipments = $order->getShipmentsCollection();
// foreach ($shipments as $shipment){
//     $shipment->delete();
// }

// // Reset item shipment qty
// // see Mage_Sales_Model_Order_Item::getSimpleQtyToShip()
// $items = $order->getAllVisibleItems();
// foreach($items as $i){
//    $i->setQtyShipped(0);
//    $i->save();
// }

// //Reset order state
// $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Undo Shipment');
// $order->save();
// die('damage done.');

        // $order = Mage::getModel('sales/order')->load('200014822', 'increment_id');
        // $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
        // $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
        // $order->save();

        // foreach($order->getAllItems() as $item) {
        //     $item->setState(2);
        //     $item->setQtyToRefund(0);
        //     $item->save();
        // }
        // $order->save();
        // die('damage done.');
    }

    // public function setOrderStatusAction() {
    //     $order = Mage::getModel('sales/order')->load('200014822', 'increment_id');
    //     $creditMemos = $order->getCreditmemosCollection();
    //     foreach($creditMemos as $cm){   //cancel each credit memo for the order
    //         $state = $cm->getState();

    //         if($cm->getId() !== '2156') {
    //             echo 'springt!';

    //             $cm->cancel();
    //             $cm->save();
    //             $cm->getOrder()->save();   //Needed to save the order to apply the canceled credit memo to all order items.

    //             // $cm->delete();
    //         }
    //         // if($state == 3){//Cancled
    //         //     continue;
    //         // }

    //         // $cm->cancel()
    //         // ->save()
    //         // ->getOrder()->save();   //Needed to save the order to apply the canceled credit memo to all order items.

    //         // $cm->delete();
    //     }

    //     // $order->save();
    //     die('damage done.');
    // }

    public function checkProductCategoryAssignmentsAction() {
        // if(!Mage::getSingleton('admin/session')->isLoggedIn()) {
        //     die('no admin!');
        //     $this->_redirect('aktionen');
        //     return;
        // }

        $productSku = $this->getRequest()->getParam("sku");
        $productId = Mage::getModel('catalog/product')->getResource()->getIdBySku($productSku);

        // echo 'qty: ' . Mage::helper('warehouse/product_base')->getAvailableQty($productId, 0) '<br/><br/>';

        $nonFlashsaleCategory = Mage::helper('aktionen')->hasCategoriesWithoutFlashsaleReferenceByProductId($productId);
        echo "<h2>Kategorienverkn&uuml;pfung f&uuml;r " . $productSku . " (product-id: " . $productId . ")</h2>";
        echo "Artikel besitzt Verkn&uumlpfung mit Nicht-Aktions-Kategorie: <strong>" . ($nonFlashsaleCategory ? 'JA' : 'NEIN')
            . "</strong><br/>";
        echo "Entnahme aus <strong>" . ($nonFlashsaleCategory ? 'HAUPTLAGER' : 'DEFAULT')
            . "</strong><br/><br/><br/>";

        $parentProductIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                  ->getParentIdsByChild($productId);

        if(count($parentProductIds)==0) {
            $parentProductIds = array($productId);
        }

        $this->listAssignedCategoriesForProductGroup($parentProductIds);
        die();
    }

    protected function listAssignedCategoriesForProductGroup($productIds) {

        if(count($productIds) <= 0)
            return;

        foreach ($productIds as $parentId) {
            $parent = Mage::getModel('catalog/product')->load($parentId);
            $this->getAssignedCategoriesForProduct($parent);

            $childProducts = Mage::getModel('catalog/product_type_configurable')
                                ->getUsedProducts(null,$parent);

            foreach ($childProducts as $product) {
                $this->getAssignedCategoriesForProduct($product);
            }
        }
    }

    protected function getAssignedCategoriesForProduct($product) {

        echo "<strong>SKU: " . $product->getSKU() . "</strong><br/>";
        echo "Categories: ";
        foreach ($product->getCategoryIds() as $categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            echo $category->getName() . "&#9;";
        }
        echo "<br/><br/>";
    }
}
