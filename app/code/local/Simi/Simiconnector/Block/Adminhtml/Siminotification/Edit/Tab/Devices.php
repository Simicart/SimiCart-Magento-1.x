<?php


class Simi_Simiconnector_Block_Adminhtml_Siminotification_Edit_Tab_Devices extends Mage_Adminhtml_Block_Widget_Grid {

    public $storeview_id;

    public function __construct($arguments = array()) {
        parent::__construct($arguments);
        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId('skuChooserGrid_' . $this->getId());
        }
        $form = $this->getJsFormObject();
        $gridId = $this->getId();
        $this->setCheckboxCheckCallback("constructDataDevice($gridId)");
        $this->setDefaultSort('device_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
        $this->setTemplate('simiconnector/siminotification/devicegrid.phtml');
    }

    /**
     * Retrieve quote store object
     * @return Mage_Core_Model_Store
     */
    public function getStore() {
        return Mage::app()->getStore();
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_devices') {
            $selected = $this->_getSelectedDevices();
            if (empty($selected)) {
                $selected = '';
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('device_id', array('in' => $selected));
            } else {
                $this->getCollection()->addFieldToFilter('device_id', array('nin' => $selected));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare Catalog Product Collection for attribute SKU in Promo Conditions SKU chooser
     *
     * @return Mage_Adminhtml_Block_Promo_Widget_Chooser_Sku
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('simiconnector/device')->getCollection()->addFieldToFilter('storeview_id',$this->storeview_id);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Define Cooser Grid Columns and filters
     *
     * @return Mage_Adminhtml_Block_Promo_Widget_Chooser_Sku
     */
    protected function _prepareColumns() {

        $this->addColumn('in_devices', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_devices',
            'values' => $this->_getSelectedDevices(),
            'align' => 'center',
            'index' => 'in_devices',
            'use_index' => true,
            'width' => '50px',
            'renderer' => 'simiconnector/adminhtml_siminotification_edit_tab_renderer_devices'
        ));

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

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/chooseDevices', array(
                    '_current' => true,
                    'current_grid_id' => $this->getId(),
                    'selected_ids' => implode(',', $this->_getSelectedDevices()),
                    'storeview_id' => $this->storeview_id,
                    'collapse' => null
        ));
    }

    protected function _getSelectedDevices() {
        $devices = $this->getRequest()->getPost('selected', array());
        if (!$devices) {
            if ($this->getRequest()->getParam('selected_ids')) {
                $devices = explode(',', $this->getRequest()->getParam('selected_ids'));
            }
        }
        return $devices;
    }

}
