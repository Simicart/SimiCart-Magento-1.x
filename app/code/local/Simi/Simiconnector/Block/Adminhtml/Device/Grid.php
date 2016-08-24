<?php

/**

 */
class Simi_Simiconnector_Block_Adminhtml_Device_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('deviceGrid');
        $this->setDefaultSort('device_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('simiconnector/device')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('device_id', array(
            'header' => Mage::helper('simiconnector')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'device_id',
        ));

        $this->addColumn('user_email', array(
            'header' => Mage::helper('simiconnector')->__('Customer Email'),
            'width' => '150px',
            'index' => 'user_email'
        ));

        $this->addColumn('plaform_id', array(
            'header' => Mage::helper('simiconnector')->__('Device Type'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'plaform_id',
            'type' => 'options',
            'options' => array(
                3 => Mage::helper('simiconnector')->__('Android'),
                1 => Mage::helper('simiconnector')->__('iPhone'),
                2 => Mage::helper('simiconnector')->__('iPad'),
            ),
        ));

        $this->addColumn('city', array(
            'header' => Mage::helper('simiconnector')->__('City'),
            'width' => '150px',
            'index' => 'city',
        ));

        $this->addColumn('state', array(
            'header' => Mage::helper('simiconnector')->__('State/Province'),
            'width' => '150px',
            'index' => 'state',
        ));

        $this->addColumn('country', array(
            'header' => Mage::helper('simiconnector')->__('Country'),
            'width' => '150px',
            'index' => 'country',
            'type' => 'options',
            'options' => Mage::helper('simiconnector/siminotification')->getListCountry(),
        ));


        $this->addColumn('is_demo', array(
            'header' => Mage::helper('simiconnector')->__('Is Demo'),
            'width' => '50px',
            'align' => 'right',
            'index' => 'is_demo',
            'type' => 'options',
            'options' => array(
                3 => Mage::helper('simiconnector')->__('N/A'),
                0 => Mage::helper('simiconnector')->__('NO'),
                1 => Mage::helper('simiconnector')->__('YES'),
            ),
        ));

        $this->addColumn('created_time', array(
            'header' => Mage::helper('simiconnector')->__('Created Date'),
            'width' => '150px',
            'align' => 'right',
            'index' => 'created_time',
            'type' => 'datetime'
        ));
        
        $this->addColumn('app_id', array(
            'header' => Mage::helper('simiconnector')->__('App Id'),
            'width' => '100px',
            'align' => 'right',
            'index' => 'app_id'
        ));
        
        $this->addColumn('build_version', array(
            'header' => Mage::helper('simiconnector')->__('Build Version'),
            'width' => '50px',
            'align' => 'right',
            'index' => 'build_version'
        ));
        /*
        $this->addColumn('device_token', array(
            'header' => Mage::helper('simiconnector')->__('Device Token'),
            'width' => '150px',
            'align' => 'right',
            'index' => 'device_token'
        ));
        */
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
        
        $this->addColumn('action', array(
            'header' => Mage::helper('simiconnector')->__('Action'),
            'width' => '80px',
            'type' => 'action',
            'getter' => 'getId',
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
        $this->setMassactionIdField('notice_id');
        $this->getMassactionBlock()->setFormFieldName('siminotification');

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
