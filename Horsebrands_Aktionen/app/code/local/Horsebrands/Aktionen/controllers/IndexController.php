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
		$this->_initLayoutMessages('core/session');

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

		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$this->_redirect('aktionen');
			return;
		}

		$this->loadLayout();
		$this->renderLayout();
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
}
