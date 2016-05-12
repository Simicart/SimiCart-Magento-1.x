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

    /**
     * prepare collection for block to display
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Grid
     */
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

    /**
     * prepare columns for this grid
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Grid
     */
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

        $this->addColumn('website_id', array(
            'header' => Mage::helper('simiconnector')->__('Website'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'website_id',
            'type' => 'options',
            'options' => Mage::getSingleton('simiconnector/status')->getWebGird(),
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

//        $this->addExportType('*/*/exportCsv', Mage::helper('connector')->__('CSV'));
//        $this->addExportType('*/*/exportXml', Mage::helper('connector')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * prepare mass action for this grid
     *
     * @return Magestore_Madapter_Block_Adminhtml_Madapter_Grid
     */
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

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
