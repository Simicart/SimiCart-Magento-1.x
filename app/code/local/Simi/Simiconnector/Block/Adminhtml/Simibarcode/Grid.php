<?php

class Simi_Simiconnector_Block_Adminhtml_Simibarcode_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('simibarcodeGrid');
        $this->setDefaultSort('barcode_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }


    protected function _prepareCollection() {
        $collection = Mage::getModel('simiconnector/simibarcode')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns() {
        $this->addColumn('barcode_id', array(
            'header' => Mage::helper('simiconnector')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'barcode_id',
        ));

        $this->addColumn('barcode', array(
            'header' => Mage::helper('simiconnector')->__('Barcode'),
            'align' => 'left',
            'index' => 'barcode',
        ));

        $this->addColumn('qrcode', array(
            'header' => Mage::helper('simiconnector')->__('QRcode'),
            'align' => 'left',
            'index' => 'qrcode',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('simiconnector')->__('Product Name'),
            'align' => 'left',
            'index' => 'product_name',
        ));

        $this->addColumn('product_sku', array(
            'header' => Mage::helper('simiconnector')->__('Product Sku'),
            'align' => 'left',
            'index' => 'product_sku',
        ));

        $this->addColumn('created_date', array(
            'header' => Mage::helper('simiconnector')->__('Created Date'),
            'align' => 'left',
            'index' => 'created_date',
            'type' => 'datetime'
        ));

        $this->addColumn('barcode_status', array(
            'header' => Mage::helper('simiconnector')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'barcode_status',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
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

        $this->addExportType('*/*/exportCsv', Mage::helper('simiconnector')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('simiconnector')->__('XML'));

        return parent::_prepareColumns();
    }


    protected function _prepareMassaction() {
        $this->setMassactionIdField('barcode_id');
        $this->getMassactionBlock()->setFormFieldName('simibarcode');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('simiconnector')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('simiconnector')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('simiconnector/status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('simiconnector')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('simiconnector')->__('Status'),
                    'values' => $statuses
                ))
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
