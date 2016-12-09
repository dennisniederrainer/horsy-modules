<?php
/**
 * User: Vitali Fehler
 * Date: 01.07.13
 */
class Horsebrands_NewsletterAdvanced_Block_Adminhtml_Type_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('type_id');
        $this->setId('horsebrands_newsletter_advanced_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('newsletteradvanced/type_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        // Add the columns that should appear in the grid
        $this->addColumn('type_name',
            array(
                'header'=> $this->__('Name'),
                'align' =>'left',
                'width' => '50px',
                'index' => 'type_name'
            )
        );

        $this->addColumn('type_frequency',
            array(
                'header'=> $this->__('Häufigkeit'),
                'align' =>'left',
                'width' => '50px',
                'index' => 'type_frequency'
            )
        );

        $this->addColumn('type_description',
            array(
                'header'=> $this->__('Beschreibung'),
                'align' =>'left',
                'width' => '50px',
                'index' => 'type_name'
            )
        );

        $this->addColumn('list_id',
            array(
                'header'=> $this->__('CleverReach List ID'),
                'align' =>'left',
                'width' => '50px',
                'index' => 'list_id'
            )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        // This is where our row data will link to
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
?>