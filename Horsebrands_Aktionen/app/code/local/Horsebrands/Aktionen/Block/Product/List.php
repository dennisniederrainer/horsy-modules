<?php

/**
 * Created by JetBrains PhpStorm.
 * User: Vitali Fehler
 * Date: 11.02.13
 */
class Horsebrands_Aktionen_Block_Product_List extends Mage_Catalog_Block_Product_List {

    //@denno 2014-03-16: Outletvariablen
    public $_outletStockName = 'Hauptlager';
    public $_outletStockId = 2;
    protected $_productcollectionOC = null;

    // protected function _getProductCollection() {
    //     if (is_null($this->_productCollection)) {
    //         $layer = $this->getLayer();
    //         /* @var $layer Mage_Catalog_Model_Layer */
    //         if ($this->getShowRootCategory()) {
    //             $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
    //         }

    //         // if this is a product view page
    //         if (Mage::registry('product')) {
    //             // get collection of categories this product is associated with
    //             $categories = Mage::registry('product')->getCategoryCollection()
    //                     ->setPage(1, 1)
    //                     ->load();
    //             // if the product is associated with any category
    //             if ($categories->count()) {
    //                 // show products from this category
    //                 $this->setCategoryId(current($categories->getIterator()));
    //             }
    //         }

    //         $origCategory = null;
    //         if ($this->getCategoryId()) {
    //             $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
    //             if ($category->getId()) {
    //                 $origCategory = $layer->getCurrentCategory();
    //                 $layer->setCurrentCategory($category);
    //             }
    //         }
    //         $this->_productCollection = $layer->getProductCollection();

    //         //wenn outlet werden produkte anders gefiltert
    //         $isOutlet = (strtolower(Mage::registry('current_category')->getName()) == 'outlet');
    //         if ($isOutlet) {
    //             $outletcategory = Mage::getModel('catalog/category')->load(258);
    //             $this->_productCollection
    //                     ->addFinalPrice()
    //                     ->getSelect()
    //                     ->joinLeft(array('prodrel' => 'catalog_product_relation'), 'e.entity_id=prodrel.parent_id', array('prodrel.child_id'))
    //                     ->join(array('inventory_item' => 'cataloginventory_stock_item'), 'prodrel.child_id=inventory_item.product_id OR e.entity_id=inventory_item.product_id', array('inventory_item.qty'))
    //                     ->where('inventory_item.stock_id = ' . $this->_outletStockId .
    //                             ' AND inventory_item.qty > 0' .
    //                             ' AND (inventory_item.qty - inventory_item.stock_reserved_qty) > 0')
    //                     ->group(array('e.entity_id'))
    //                     ->order($outletcategory->getDefaultSortBy(), 'ASC');
    //         }
    //         $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

    //         //Vor-Zurück_Funktion
    //         Mage::getSingleton('core/session')->setData('productids', $this->_productCollection->getAllIdsInDefaultOrder());

    //         if ($origCategory) {
    //             $layer->setCurrentCategory($origCategory);
    //         }
    //     }
        
    //     return $this->_productCollection;
    // }

    public function getOutletProductList($productcollection) {
        $category = Mage::getModel('catalog/category')->load(258);
        $configurableProductForCategory = $productcollection//Mage::getModel('catalog/product')->getCollection()
                ->addFinalPrice()
                ->addCategoryFilter($category)
                ->joinField('child_id', 'catalog/product_relation', 'child_id', 'parent_id=entity_id', null, 'left')
                ->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=child_id', '{{table}}.stock_id=2', 'left')
                ->addAttributeToFilter('qty', array('gt' => 0));
        $configurableProductForCategory->getSelect()->group('e.entity_id');
//        echo 'config: ' . count($configurableProductForCategory);

        $simpleProductForCategory = $productcollection//Mage::getModel('catalog/product')->getCollection()
                ->addFinalPrice()
                ->addCategoryFilter($category)
                ->joinField('parent_id', 'catalog/product_relation', 'parent_id', 'child_id=entity_id', null, 'left')
                ->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=2', 'left')
                ->addAttributeToFilter('qty', array('gt' => 0))
                ->addAttributeToFilter('parent_id', array('null' => true));
        $simpleProductForCategory->getSelect()->group('e.entity_id');
//        echo '<br/>simple: ' . count($simpleProductForCategory);

        foreach ($simpleProductForCategory as $simpleproduct) {
            $configurableProductForCategory->addItem($simpleproduct);
        }

        return $configurableProductForCategory;
    }

    public function getCatalogFilterHtml() {
        return $this->getChildChildHtml('catalog_filter');
    }

    /* @denno: 2014-03-11
     * Helper Methode um Outlet-Lager-Id zu erfassen
     * !! FUNKTIONIERT NICHT, da Model so nicht vorhanden !!
     */

    protected function _getOutletStockId($name) {
        $stock = Mage::getModel('cataloginventory/stock')->getCollection()
                ->addFieldToFilter('stock_name', $name)
                ->getFirstItem();

        if ($stock->getId())
            return $stock->getId();

        return 0;
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
        if ($this->_outletStockId == null)
            $this->_outletStockId = $this->_getOutletStockId($this->_outletStockName);

        //wenn configurable wird geprüft, ob min. ein verknüpfter artikel outlet-bestand hat, damit es angezeigt werden kann
        if ($product->isConfigurable()) {
            $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);

            foreach ($childProducts as $_childProduct) {
                if ($this->isOutletSaleable($_childProduct))
                    return true;
            }
        }else {
            //stockitem für das produkt im hauptlager laden
            $stockitem = Mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($product->getId(), $this->_outletStockId);

            //fehler oder 'out of stock'?
            if (!$stockitem || !$stockitem->getIsInStock())
                return false;

            $availableQty = Mage::getModel('cataloginventory/stock_item')
                    ->loadByProductWarehouse($product->getId(), $this->_outletStockId)
                    ->getAvailableQty();
        }

        if ($availableQty > 0)
            return true;
        else
            return false;
    }

}

?>