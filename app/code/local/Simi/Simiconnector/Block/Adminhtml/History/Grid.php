<?php
/**
 * 

 */
class Simi_Simiconnector_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct() {
        parent::__construct();
        $this->setId('noticeGrid');
        $this->setDefaultSort('history_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('simiconnector/history')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('history_id', array(
            'header' => Mage::helper('simiconnector')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'history_id',
        ));

        $this->addColumn('notice_title', array(
            'header' => Mage::helper('simiconnector')->__('Title'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'notice_title',
        ));

        $this->addColumn('notice_content', array(
            'header' => Mage::helper('simiconnector')->__('Message'),
            'align' => 'left',
            'index' => 'notice_content',
        ));

        $storeOptions = array();
        foreach (Mage::getModel('core/store')->getCollection() as $store) {
            $storeOptions [$store->getId()] = $store->getName(); 
        }
        $this->addColumn('storeview_id', array(
            'header' => Mage::helper('simiconnector')->__('Store View'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'storeview_id',
            'type' => 'options',
            'options' => $storeOptions,
        ));

        $this->addColumn('device_id', array(
            'header' => Mage::helper('simiconnector')->__('Device'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'device_id',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('simiconnector')->__('All'),
                1 => Mage::helper('simiconnector')->__('IOS'),
                2 => Mage::helper('simiconnector')->__('Android'),
            ),
        ));


        $this->addColumn('notice_type', array(
            'header' => Mage::helper('simiconnector')->__('Type'),
            'align' => 'left',
            'width' => '120px',
            'index' => 'notice_type',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('simiconnector')->__('Custom'),
                1 => Mage::helper('simiconnector')->__('Price Updates'),
                2 => Mage::helper('simiconnector')->__('New Product'),
                3 => Mage::helper('simiconnector')->__('Order Purchase'),
            ),
        ));

        $this->addColumn('created_time', array(
                'header'    => Mage::helper('simiconnector')->__('Sent Date'),
                'width'     => '150px',
                'index'     => 'created_time',
                'type'      => 'datetime',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('simiconnector')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => Mage::helper('simiconnector')->__('Successfully'),
                0 => Mage::helper('simiconnector')->__('Unsuccessfully'),
            ),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('simiconnector')->__('Action'),
            'width' => '60px',
            'type' => 'action',
            'getter' => 'getId',
            'align' => 'left',
            'actions' => array(
                array(
                    'caption' => Mage::helper('simiconnector')->__('View'),
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

    /**
     * prepare mass action for this grid
     *
     * @return Magestore_Madapter_Block_Adminhtml_Madapter_Grid
     */
    protected function _prepareMassaction() {
        $this->setMassactionIdField('history_id');
        $this->getMassactionBlock()->setFormFieldName('history');

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