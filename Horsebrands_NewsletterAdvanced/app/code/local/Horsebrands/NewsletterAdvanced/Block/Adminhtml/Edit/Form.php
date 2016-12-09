<?php
/**
 * User: Vitali Fehler
 * Date: 01.07.13
 */
class Horsebrands_NewsletterAdvanced_Block_Adminhtml_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('horsebrands_newsletteradvanced_type_form');
        $this->setTitle($this->__('Newsletter List'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('newsletteradvanced_type');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('checkout')->__('Newsletter List'),
            'class'     => 'fieldset-wide',
        ));

        $fieldset->addField('type_name', 'text', array(
            'name'      => 'type_name',
            'label'     => Mage::helper('checkout')->__('Name'),
            'title'     => Mage::helper('checkout')->__('Name'),
            'required'  => true,
        ));
        $fieldset->addField('type_frequency', 'text', array(
            'name'      => 'type_frequency',
            'label'     => Mage::helper('checkout')->__('Häufigkeit'),
            'title'     => Mage::helper('checkout')->__('Häufigkeit'),
            'required'  => false,
        ));
        $fieldset->addField('type_description', 'textarea', array(
            'name'      => 'type_description',
            'label'     => Mage::helper('checkout')->__('Beschreibung'),
            'title'     => Mage::helper('checkout')->__('Beschreibung'),
            'required'  => false,
        ));
        $fieldset->addField('list_id', 'text', array(
            'name'      => 'list_id',
            'label'     => Mage::helper('checkout')->__('CleverReach List ID'),
            'title'     => Mage::helper('checkout')->__('CleverReach List ID'),
            'required'  => true,
        ));

        if ($model->getId()) {
            $fieldset->addField('type_id', 'hidden', array(
                'name' => 'type_id',
            ));
            $form->setValues($model->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
?>