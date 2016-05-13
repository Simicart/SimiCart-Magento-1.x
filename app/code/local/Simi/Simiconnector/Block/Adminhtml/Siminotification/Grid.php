<?php
/**

 */
class Simi_Simiconnector_Block_Adminhtml_Siminotification_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct() {
        parent::__construct();
        $this->setId('noticeGrid');
        $this->setDefaultSort('notice_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('simiconnector/siminotification')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('notice_id', array(
            'header' => Mage::helper('simiconnector')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'notice_id',
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

        $this->addColumn('website_id', array(
            'header' => Mage::helper('simiconnector')->__('Website'),
            'width' => '100px',
            'index' => 'website_id',
            'renderer' => 'simiconnector/adminhtml_grid_renderer_website',
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

        $this->addColumn('country', array(
                'header'    => Mage::helper('simiconnector')->__('Country'),
                'width'     => '150px',
                'index'     => 'country',
                'type'      => 'options',
                'options'   => Mage::helper('simiconnector/siminotification')->getListCountry(),
        ));

        $this->addColumn('created_time', array(
                'header'    => Mage::helper('simiconnector')->__('Created Date'),
                'width'     => '150px',
                'index'     => 'created_time',
                'type'      => 'datetime',
        ));


        $this->addColumn('action', array(
            'header' => Mage::helper('simiconnector')->__('Action'),
            'width' => '60px',
            'type' => 'action',
            'getter' => 'getId',
            'align' => 'left',
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

    /**
     * prepare mass action for this grid
     *
     * @return Magestore_Madapter_Block_Adminhtml_Madapter_Grid
     */
    protected function _prepareMassaction() {
        $this->setMassactionIdField('notice_id');
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