<?php
class Horsebrands_Aktionen_Model_Category extends Mage_Catalog_Model_Category
{

    //#### Outlet ####
    public $_outletStockName = 'Hauptlager';
    public $_outletStockId = 2;
    
    protected $_outletname = 'outlet';
    protected $_productcollectionOC = null;

    protected function _afterSave() {
        Mage::log('category after save.', null, 'horsy_cat.log');
        parent::_afterSave();
    }

    public function getProductCollection() {
        
        if(strtolower($this->getName())==$this->_outletname){

            if(is_null($this->_productcollectionOC)) {
                $this->_productcollectionOC = parent::getProductCollection();
                
                foreach( $this->_productcollectionOC->getItems() as $key=>$product ) {
                    if(!$this->isOutletSaleable($product)) {
                        $this->_productcollectionOC->removeItemByKey($key);
                    }
                }
            }
                    
            return $this->_productcollectionOC;
        } else {
            return parent::getProductCollection();
        }
    }

    /* @denno: 2014-03-11
     * isOutletSaleable(Mage_Advancedstock_Model_Catalog_Product)
     *  Methode prüft, ob ein Product Hauptlagerbestand hat.
     *  Bei einem Konfigurierbaren Product werden die Child-Products darauf überprüft - welche Products dann wirklich
     *    in der Auswahlliste des Config-Product angezeigt werden, geschiet in der Product_View
     * return boolean
     */
    public function isOutletSaleable($product) {
        $availableQty = 0;

        //check if outlet-stock exists
        if($this->_outletStockId == null)
            $this->_outletStockId = $this->_getOutletStockId($this->_outletStockName);

        //wenn configurable wird geprüft, ob min. ein verknüpfter artikel outlet-bestand hat, damit es angezeigt werden kann
        if($product->isConfigurable()) {
            $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);

            foreach($childProducts as $_childProduct) {
                if($this->isOutletSaleable($_childProduct))
                    return true;
            }

        }else {
            //stockitem für das produkt im hauptlager laden
            $stockitem = Mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($product->getId(), $this->_outletStockId);

            //fehler oder 'out of stock'?
            if(!$stockitem || !$stockitem->getIsInStock())
                return false;

            $availableQty = Mage::getModel('cataloginventory/stock_item')
                                ->loadByProductWarehouse($product->getId(), $this->_outletStockId)
                                ->getAvailableQty();
        }

        if($availableQty>0)
            return true;
        else
            return false;
    }


    //#### external referencing ####

	/**
	 * @denno 2014-03-18: overwrite
	 */
    public function getUrl() {
    	return parent::getUrl();
    }

    /**
     * @denno 2014-03-18
     *	returns boolean
     */
    protected function _isExternalRedirect() {
    	$urlKey = '-extern';
    	$url = parent::getUrl();

    	//wenn Key in URL vorkommt ist es KEINE externe Weiterleitung
    	if(strpos(strtolower($url), $urlKey))
    		return true;

    	return false;
    }

    /**
     * @denno 2014-03-18
     *	returns string (<a target="..." /a>)
     */
    public function getTargetString() {
    	$targetstring = '';

    	if($this->_isExternalRedirect()) {
			$targetstring = ' target="_blank"';
		}

    	return $targetstring;
    }
}