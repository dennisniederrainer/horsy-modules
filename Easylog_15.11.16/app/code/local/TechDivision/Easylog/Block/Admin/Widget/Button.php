<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

/**
 * @category       TechDivision
 * @package        TechDivision_Easylog
 * @copyright      Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license        http://opensource.org/licenses/osl-3.0.php
 *                 Open Software License (OSL 3.0)
 */
class TechDivision_Easylog_Block_Admin_Widget_Button
    extends Mage_Adminhtml_Block_Widget_Button
{
    protected function _prepareLayout()
    {
        $this->setData('id', 'button_easylog_export');
        $this->setData('label', Mage::helper('easylog')->__('Easylog Export'));
        $this->setData('on_click', "
            setLocation('".
                $this->getUrl('adminhtml/manage/add', array(
                    'order_id' => $this->getRequest()->getParam('order_id')
                ))
            ."');
        ");
        $this->setData('after_html', "
            <script type='text/javascript'>
                var button = $('button_easylog_export');
                var buttonClone = button.cloneNode(true);
                button.remove();
                $$('.form-buttons').first().insert({
                    bottom: buttonClone
                });
            </script>
        ");

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('admin/easylog/manage')) {
            return '';
        }

        return parent::_toHtml();
    }
}
