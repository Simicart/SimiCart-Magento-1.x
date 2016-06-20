<?php

class Simi_Simiconnector_Block_Adminhtml_Simibarcode_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        if (Mage::getModel('admin/session')->getData('barcode_product_import')) {
            $this->setDefaultFilter(array('in_products' => 1));
        }
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds))
                $productIds = 0;
            if ($column->getFilter()->getValue())
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            elseif ($productIds)
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
            return $this;
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareCollection() {

        // $collection = Mage::getModel('catalog/product')->getCollection()
        //         ->addAttributeToSelect('sku')
        //         ->addAttributeToSelect('name')
        //         ->addAttributeToSelect('status')
        //         ->addAttributeToSelect('price')
        //         ->addAttributeToSelect('attribute_set_id')
        //         ->addAttributeToSelect('type_id')
        //         ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
        // $this->setCollection($collection);
        // return parent::_prepareCollection();

        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('attribute_set_id')
                ->addAttributeToSelect('type_id');

        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                    'name', 'catalog_product/name', 'entity_id', null, 'inner', $adminStore
            );
            $collection->joinAttribute(
                    'visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId()
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        $collection->addAttributeToFilter('visibility', array('nin' => array('1')));
        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _prepareColumns() {
        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_products',
            'values' => $this->_getSelectedProducts(),
            'align' => 'center',
            'index' => 'entity_id'
        ));

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('simiconnector')->__('ID'),
            'sortable' => true,
            'width' => '50px',
            'type' => 'number',
            'index' => 'entity_id',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('simiconnector')->__('Name'),
            'align' => 'left',
            'index' => 'name'
        ));

        $this->addColumn('type', array(
            'header' => Mage::helper('catalog')->__('Type'),
            'width' => '60px',
            'index' => 'type_id',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();

        $this->addColumn('set_name', array(
            'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width' => '100px',
            'index' => 'attribute_set_id',
            'type' => 'options',
            'options' => $sets,
        ));

        $this->addColumn('product_sku', array(
            'header' => Mage::helper('simiconnector')->__('SKU'),
            'width' => '80px',
            'index' => 'sku'
        ));

        $this->addColumn('barcode', array(
            'header' => Mage::helper('simiconnector')->__('Barcode'),
            'align' => 'left',
            'width' => '130px',
            'index' => 'barcode',
            'type' => 'input',
            'editable' => true,
            'edit_only' => true,
            'renderer' => 'simiconnector/adminhtml_simibarcode_edit_renderer_barcodecustom',
        ));

        $this->addColumn('qrcode', array(
            'header' => Mage::helper('simiconnector')->__('QR code'),
            'align' => 'left',
            'width' => '130px',
            'index' => 'qrcode',
            'type' => 'input',
            'editable' => true,
            'edit_only' => true,
            'renderer' => 'simiconnector/adminhtml_simibarcode_edit_renderer_qrcodecustom',
        ));

        $store = $this->_getStore();
        // $this->addColumn('price',
        //     array(
        //         'header'=> Mage::helper('catalog')->__('Price'),
        //         'type'  => 'price',
        //         'currency_code' => $store->getBaseCurrency()->getCode(),
        //         'index' => 'price',
        // ));
        // $this->addColumn('visibility',
        //     array(
        //         'header'=> Mage::helper('catalog')->__('Visibility'),
        //         'width' => '70px',
        //         'index' => 'visibility',
        //         'type'  => 'options',
        //         'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        // ));

        $this->addColumn('status', array(
            'header' => Mage::helper('simiconnector')->__('Status'),
            'width' => '90px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('simiconnector')->__('Action'),
            'width' => '70px',
            'align' => 'center',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('simiconnector')->__('View'),
                    'url' => array('base' => 'adminhtml/catalog_product/edit'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));


        return parent::_prepareColumns();
    }

    public function _getSelectedProducts() {
        $productArrays = $this->getProducts();
        $products = '';
        $warehouseProducts = array();
        if ($productArrays) {
            $products = array();
            foreach ($productArrays as $productArray) {
                parse_str(urldecode($productArray), $warehouseProducts);
                if (count($warehouseProducts)) {
                    foreach ($warehouseProducts as $pId => $enCoded) {
                        $products[] = $pId;
                    }
                }
            }
        }
        if (!is_array($products) || Mage::getModel('admin/session')->getData('barcode_product_import')) {
            $products = array_keys($this->getSelectedProducts());
        }

        return $products;
    }

    public function getSelectedProducts() {

        $products = array();
        if ($barcodeProducts = Mage::getModel('admin/session')->getData('barcode_product_import')) {
            foreach ($barcodeProducts as $barcodeProduct) {
                $products[$barcodeProduct['PRODUCT_ID']] = array('barcode' => $barcodeProduct['BARCODE']);
                $products[$barcodeProduct['PRODUCT_ID']] = array('qrcode' => $barcodeProduct['QRCODE']);
                $products[$barcodeProduct['PRODUCT_ID']] = array('barcode_status' => $barcodeProduct['BARCODE_STATUS']);
            }
        }

        return $products;
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/productsGrid', array(
                    '_current' => true
        ));
    }

    public function getRowUrl($row) {
        return false;
    }

}
