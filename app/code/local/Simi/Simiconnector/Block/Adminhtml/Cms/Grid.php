<?php

/**

 */
class Simi_Simiconnector_Block_Adminhtml_Cms_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('noticeGrid');
        $this->setDefaultSort('cms_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $webId = 0;
        $collection = Mage::getModel('simiconnector/cms')->getCollection();
        if ($this->getRequest()->getParam('website')) {
            $webId = $this->getRequest()->getParam('website');
            $collection->addFieldToFilter('website_id', array('eq' => $webId));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('cms_id', array(
            'header' => Mage::helper('simiconnector')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'cms_id',
        ));

        $this->addColumn('cms_title', array(
            'header' => Mage::helper('simiconnector')->__('Title'),
            'align' => 'left',
            'index' => 'cms_title',
        ));

        
        $this->addColumn('sort_order', array(
            'header' => Mage::helper('simiconnector')->__('Sort Order'),
            'align' => 'left',
            'width' => '50px',
            'index' => 'sort_order',
            'filter' => false
        ));


        $this->addColumn('cms_status', array(
            'header' => Mage::helper('simiconnector')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'cms_status',
            'type' => 'options',
            'options' => array(
                1 => 'Yes',
                0 => 'No',
            ),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('simiconnector')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('simiconnector')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));


        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('cms_id');
        $this->getMassactionBlock()->setFormFieldName('simiconnector');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('simiconnector')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('simiconnector')->__('Are you sure?')
        ));

        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
