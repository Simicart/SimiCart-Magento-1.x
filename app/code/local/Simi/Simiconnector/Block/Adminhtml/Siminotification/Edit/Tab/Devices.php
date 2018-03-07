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

        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);
                if (isset($data['count_purchase']) && $data['count_purchase']) {
                    $from_count_purchase = $data['count_purchase']['from'];
                    $to_count_purchase = $data['count_purchase']['to'];
                }
                if (isset($data['city']) && $data['city']) {
                    $array = explode('_', $data['city']);
                    if (count($array) > 1) {
                        $data['city'] = $array[1];
                    }
                    $city = $data['city'];
                }

                if (isset($data['state']) && $data['state']) {
                    $state = $data['state'];
                }

                $this->_setFilterValues($data);
            } else if ($filter && is_array($filter)) {

                if (isset($filter['count_purchase']) && $filter['count_purchase']) {
                    $from_count_purchase = $filter['count_purchase']['from'];
                    $to_count_purchase = $filter['count_purchase']['to'];
                }
                //unset($filter['count_purchase']);

                if (isset($filter['city']) && $filter['city']) {
                    $array = explode('_', $filter['city']);
                    if (count($array) > 1) {
                        $filter['city'] = $array[1];
                    }
                    $city = $filter['city'];
                }

                if (isset($filter['state']) && $filter['state']) {
                    $state = $filter['state'];
                    unset($filter['state']);
                }

                $this->_setFilterValues($filter);
            } else if (0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir) == 'desc') ? 'desc' : 'asc';
                $this->_columns[$columnId]->setDir($dir);
                $this->_setCollectionOrder($this->_columns[$columnId]);
            }

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }


        $new_collection = new Varien_Data_Collection();

        foreach ($collection as $device) {
            $item = new Varien_Object();
            $item->setData($device->toArray());

            $customer_email = $device['user_email'];
            $size = 0;
            if ($customer_email) {
                $orders = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToFilter('customer_email', $customer_email);
                $size = $orders->getSize();

            }
            $item->setData('count_purchase', $size);
            if (!$this->filterCountPurchase($from_count_purchase, $to_count_purchase, $size)) {
                continue;
            }

            if (!$this->filterCity($city, $item)) {
                continue;
            }

            if (!$this->filterState($state, $item)) {
                continue;
            }



            $new_collection->addItem($item);
        }

        $this->setCollection($new_collection);

        return $this;

    }

    protected function _setFilterValues($data)
    {
        foreach ($this->getColumns() as $columnId => $column) {

            if ($columnId == 'state' || $columnId == 'city' || $columnId == 'count_purchase') {
                $column->getFilter()->setValue($data[$columnId]);
                continue;
            }

            if (isset($data[$columnId])
                && (!empty($data[$columnId]) || strlen($data[$columnId]) > 0)
                && $column->getFilter()
            ) {
                $column->getFilter()->setValue($data[$columnId]);

                $this->_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }

    protected function filterCountPurchase($from_count_purchase, $to_count_purchase, $size)
    {
        if ($from_count_purchase && $to_count_purchase) {
            if (!$size) {
                return false;
            }

            $size = floatval($size);
            $from_count_purchase = floatval($from_count_purchase);
            $to_count_purchase = floatval($to_count_purchase);

            if ($from_count_purchase <= $size && $to_count_purchase >= $size) {
                return true;
            }
            return false;
        } else if ($from_count_purchase) {

            if (!$size) {
                return false;
            }

            if ($size >= $from_count_purchase) {
                return true;
            }
            return false;
        } else if ($to_count_purchase) {

            if (!$size) {
                return false;
            }

            if ($size <= $to_count_purchase) {
                return true;
            }
            return false;
        }
        // no filter for count_purchase
        return true;

    }

    protected function filterCity($city, $item)
    {

        if (!$city) {
            return true;
        }

        $item_city = $item->getCity();

        setLocale(LC_ALL, 'vn_VN');
        $city = preg_replace('#[^\w\s]+#', '', iconv('UTF-8', 'ASCII//TRANSLIT', $city));

        $item_city = preg_replace('#[^\w\s]+#', '', iconv('UTF-8', 'ASCII//TRANSLIT', $item_city));


        if ($item_city && strpos($item_city, $city) !== false) {
            return true;
        }

        return false;
    }

    protected function filterState($state, $item)
    {

        if (!$state) {
            return true;
        }

        $item_state = $item->getState();

        setLocale(LC_ALL, 'vn_VN');
        $state = preg_replace('#[^\w\s]+#', '', iconv('UTF-8', 'ASCII//TRANSLIT', $state));

        $item_state = preg_replace('#[^\w\s]+#', '', iconv('UTF-8', 'ASCII//TRANSLIT', $item_state));

        if ($item_state && strpos($item_state, $state) !== false) {
            return true;
        }
        return false;
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

        $this->addColumn('country', array(
            'header' => Mage::helper('simiconnector')->__('Country'),
            'width' => '150px',
            'index' => 'country',
            'type' => 'options',
            'options' => Mage::helper('simiconnector/siminotification')->getListCountry(),

        ));


        $this->addColumn('state', array(
            'header' => Mage::helper('simiconnector')->__('State'),
            'width' => '150px',
            'index' => 'state',
            'type' => 'customoptions',
            'options' => $this->getCityByCountryCode('VN')
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
        $devices = $this->getRequest()->getPost('selected', array());
        if (!$devices) {
            if ($this->getRequest()->getParam('selected_ids')) {
                $devices = explode(',', $this->getRequest()->getParam('selected_ids'));
            }
        }
        return $devices;
    }

}
