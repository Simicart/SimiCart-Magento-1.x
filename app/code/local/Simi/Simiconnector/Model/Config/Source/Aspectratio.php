<?php
/**

 */
class Simi_Simiconnector_Model_Config_Source_Aspectratio
{
    const LETTER_BOX = 1;
    const PAN_AND_SCAN = 2;

    public function toOptionArray()
    {
        return array(
            self::LETTER_BOX => Mage::helper('simiconnector')
                ->__('Letter Box (display wide image with blank stripes on top and bottom)'),
            self::PAN_AND_SCAN => Mage::helper('simiconnector')
                ->__('Pan & Scan (display full height image and got cropped by 2 sides)'),
        );
    }



    public function toArray()
    {
        return array(
            LETTER_BOX => Mage::helper('adminhtml')
                ->__('Letter Box (display wide image with blank stripes on top and bottom)'),
            PAN_AND_SCAN => Mage::helper('adminhtml')
                ->__('Pan & Scan (display full height image and got cropped by 2 sides)'),
        );
    }
    
}