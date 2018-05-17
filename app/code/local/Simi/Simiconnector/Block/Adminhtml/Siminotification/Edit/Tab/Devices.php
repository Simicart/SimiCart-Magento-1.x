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

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('simiconnector/device')->getCollection()->addFieldToFilter('storeview_id', $this->storeview_id);
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

        $country_default = Mage::getStoreConfig('general/country/default');
        $this->addColumn('country', array(
            'header' => Mage::helper('simiconnector')->__('Country'),
            'width' => '150px',
            'index' => 'country',
            'type' => 'options',
            'options' => Mage::helper('simiconnector/siminotification')->getListCountry(),
            'value' => $country_default
        ));

        $this->addColumn('state', array(
            'header' => Mage::helper('simiconnector')->__('State/Province'),
            'width' => '150px',
            'index' => 'state',
            'type' => 'customoptions',
            'options' => $this->getCityByCountryCode($country_default),
            'filter_condition_callback' => array($this, '_stateFilter'),
        ));

        $this->addColumn('city', array(
            'header' => Mage::helper('simiconnector')->__('City'),
            'width' => '150px',
            'index' => 'city',
            'type' => 'text',
        ));

        $this->addColumn('count_purchase', array(
            'header' => Mage::helper('simiconnector')->__('Order quantity'),
            'width' => '150px',
            'index' => 'count_purchase',
            'type' => 'number',

        ));

        $this->addColumn('base_url_hide', array(
            'type' => 'text',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
            'renderer' => 'Simi_Simiconnector_Block_Adminhtml_Device_Edit_Renderer_Hidden'
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


    protected function _stateFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();



        $array = explode('_', $value);

        if (count($array) > 1) {
            $value_state = $array[1];
        } else {
            $value_state = $value;
        }

        $collection->addFieldToFilter($field, array('eq' => $value_state));
        return $this;
    }


    public function addColumn($columnId, $column)
    {
        if (is_array($column)) {
            $this->_columns[$columnId] = $this->getLayout()->createBlock('simiconnector/adminhtml_siminotification_edit_tab_renderer_column')
                ->setData($column)
                ->setGrid($this);
        }
        else {
            throw new Exception(Mage::helper('adminhtml')->__('Wrong column format.'));
        }

        $this->_columns[$columnId]->setId($columnId);
        $this->_lastColumnId = $columnId;
        return $this;
    }

    public function getListCountry()
    {
        $listCountry = array();

        $collection = Mage::getResourceModel('directory/country_collection')
            ->loadByStore();

        if (count($collection)) {
            foreach ($collection as $item) {
                $listCountry[$item->getId()] = $item->getName();
            }
        }

        return $listCountry;
    }

    protected function getCityByCountryCode($country_code)
    {

        $data = array();
        $states = Mage::helper('simiconnector/siminotification')->getListState($country_code);
        if (count($states) > 0) {
            foreach ($states as $key => $state) {
                $data[$key . '_' . $state] = $state;
//                $states_response .= "<option value='" . $key . '_' . $state . "'>" . $state . " </option>";
            }
        }

        return $data;

    }

    protected function getStateByCityAndCountry()
    {
        $data = array();
//        $counties = Mage::getModel('romcity/romcity')->getCollection()->addFieldToFilter('country_id', 'VN');
//
//        if (count($counties) > 0) {
//
//            foreach ($counties as $county) {
//                $full_city_name = $county->getData('cityname');
//                $city_name = trim(str_replace('Quận', '', $full_city_name));
//                $data[$city_name] = $full_city_name;
//            }
//        }

        return $data;

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
        $devices =  Mage::getSingleton('admin/session')->getSelectedDevie();
//        $devices = $this->getRequest()->getPost('selected', array());
//        if (!$devices) {
//            if ($this->getRequest()->getParam('selected_ids')) {
//                $devices = explode(',', $this->getRequest()->getParam('selected_ids'));
//            }
//        }
        return $devices;
    }

}
