<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Livechatzopim
 * @copyright   Copyright (c) 2012 
 * @license   
 */

/**
 * Livechatzopim Model
 * 
 * @category    
 * @package     Livechatzopim
 * @author      Developer
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
class Simi_Simiconnector_Model_Config_Source_Showandhideoption
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 4, 'label'=>Mage::helper('adminhtml')->__('Required')),
            array('value' => 3, 'label'=>Mage::helper('adminhtml')->__('Optional')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Hide')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            4 => Mage::helper('adminhtml')->__('Required'),
            3 => Mage::helper('adminhtml')->__('Optional'),
            0 => Mage::helper('adminhtml')->__('Hide'),          
        );
    }

}

