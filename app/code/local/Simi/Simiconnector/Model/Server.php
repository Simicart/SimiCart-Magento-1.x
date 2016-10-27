<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/2/16
 * Time: 4:20 PM
 */
class Simi_Simiconnector_Model_Server
{

    protected $_helper;
    protected $_data = array();
    protected $_method = 'callApi';

    public function __construct()
    {
        $this->_helper = Mage::helper('simiconnector');
    }

    public function init(Simi_Simiconnector_Controller_Action $controller)
    {
        $this->initialize($controller);
        return $this;
    }

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function getData()
    {
        return $this->_data;
    }

    /**
     * @return mixed|string
     * @throws Exception
     * error code
     * 1 Not Login
     * 2 Miss username or password to login
     * 3 Access Denied
     * 4 Invalid method
     * 5 Login failed
     * 6 Resource cannot callable
     * 7 Missed input Value
     */
    public function run()
    {
        $data = $this->_data;

        if (count($data) == 0) {
            throw new Exception($this->_helper->__('Invalid method.'), 4);
        }

        if (!isset($data['resource'])) throw new Exception($this->_helper->__('Invalid method.'), 4);

        $model = Mage::getSingleton($data['module'] . '/api_' . $data['resource']);

        if (!$model) {
            throw new Exception($this->_helper->__('Invalid method.'), 4);
        }

        if (is_callable(array(&$model, $this->_method))) {
            return call_user_func_array(array(&$model, $this->_method), array($data));
        }
        throw new Exception($this->_helper->__('Resource cannot callable.'), 6);
    }

    /**
     * @param Simi_Simiconnector_Controller_Action $controller
     * $is_method = 1 - get
     * $is_method = 2 - post
     * $is_method = 3 - update
     * $is_method = 4 - delete
     */
    public function initialize(Simi_Simiconnector_Controller_Action $controller)
    {

        $request_string = $controller->getRequest()->getRequestString();
        $action_string = $controller->getRequest()->getActionName() . '/';
        $cache = explode($action_string, $request_string);
        $resources_string = $cache[1];
        $resources = explode('/', $resources_string);

        $resource = isset($resources[0]) ? $resources[0] : null;
        $resourceid = isset($resources[1]) ? $resources[1] : null;
        $nestedresource = isset($resources[2]) ? $resources[2] : null;
        $nestedid = isset($resources[3]) ? $resources[3] : null;

        $module = $controller->getRequest()->getModuleName();
        $params = $controller->getRequest()->getQuery();
        $contents = $controller->getRequest()->getRawBody(); // using without GET method
        $contents_array = array();
        if ($contents && strlen($contents)) {
            $contents_paser = urldecode($contents);
            $contents = json_decode($contents_paser);
            $contents_array = json_decode($contents_paser, true);
        }

        $is_method = 1;
        if ($controller->getRequest()->isPost()) {
            $is_method = 2;
        } elseif ($controller->getRequest()->isPut()) {
            $is_method = 3;
        } elseif ($controller->getRequest()->isDelete()) {
            $is_method = 4;
        }
        $this->_data = array(
            'resource' => $resource,
            'resourceid' => $resourceid,
            'nestedresource' => $nestedresource,
            'nestedid' => $nestedid,
            'params' => $params,
            'contents' => $contents,
            'contents_array' => $contents_array,
            'is_method' => $is_method,
            'module' => $module,
            'controller' => $controller,
        );
        Mage::dispatchEvent('simi_simiconnector_model_server_initialize', array('object' => $this, 'data' => $this->_data));
    }

}