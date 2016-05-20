<?php

/**
 * Created by PhpStorm.
 * User: Scott
 * Date: 5/19/2016
 * Time: 4:47 PM
 */
class Simi_Simiconnector_Model_Api_Categories extends Simi_Simiconnector_Model_Api_Abstract
{

    public function setBuilderQuery()
    {
        Zend_debug::dump(Mage::getStoreConfig("simiconnector/general/categories_in_app"));die;
    }
}