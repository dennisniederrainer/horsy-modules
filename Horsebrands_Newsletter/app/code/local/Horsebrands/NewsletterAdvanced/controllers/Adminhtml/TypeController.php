<?php
/**
 * User: Vitali Fehler
 * Date: 01.07.13
 */
class Horsebrands_NewsletterAdvanced_Adminhtml_TypeController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('newsletter/type')
            ->_title($this->__('Newsletteradvanced'))->_title($this->__('Type'));
        return $this;
    }
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function newAction()
    {
        // We just forward the new action to a blank edit form
        $this->_forward('edit');
    }
    public function editAction()
    {
        $this->_initAction();
        // Get id if available
        $id  = $this->getRequest()->getParam('id');
        $model = Mage::getModel('newsletteradvanced/type');
        if ($id) {
            // Load record
            $model->load($id);
            // Check if record is loaded
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This newsletter type no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        $data = Mage::getSingleton('adminhtml/session')->getNewsletterTypeData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        Mage::register('newsletteradvanced_type', $model);
        $this->renderLayout();
    }
    public function saveAction() {
        if($postData = $this->getRequest()->getPost())
        {
            $model = Mage::getModel('newsletteradvanced/type');
            $model->setData($postData);
            try
            {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess('Newsletter Typ wurde gespeichert');
                $this->_redirect('*/*/');
                return;
            }
            catch (Mage_Core_Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($this->__('An error occurred while saving this newsletter type.'));
            }
            Mage::getSingleton('adminhtml/session')->setNewsletterTypeData($postData);
            $this->_redirectReferer();
        }
    }
    public function deleteAction()
    {
        $id  = $this->getRequest()->getParam('id');
        $model = Mage::getModel('newsletteradvanced/type');
        if ($id) {
            // Load record
            $model->load($id);
            // Check if record is loaded
            if ($model->getId()) {
                try {
                    $model->delete();
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Newslettertype wurde gelöscht.'));
                }
                catch (Exception $e){
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
            $this->_redirect('*/*/');
        }
    }
}
