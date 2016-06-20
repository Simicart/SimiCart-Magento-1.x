<?php
/**

 */
class Simi_Simiconnector_Model_Config_Source_Productsviewtype
{
    const LIST_TYPE = 0;
    const GRID_TYPE = 1;

    public function toOptionArray()
    {
        return array(
            self::LIST_TYPE => Mage::helper('simiconnector')->__('List'),
            self::GRID_TYPE => Mage::helper('simiconnector')->__('Grid'),
        );
    }
}