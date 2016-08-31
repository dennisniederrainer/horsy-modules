<?php
class Horsebrands_Aktionen_KommendeController extends Mage_Core_Controller_Front_Action {
    function indexAction() {
        $this->loadLayout();

        $this->getLayout()
            ->getBlock('upcoming');

        $this->renderLayout();
    }
}
?>