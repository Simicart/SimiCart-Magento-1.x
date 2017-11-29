<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11/28/17
 * Time: 3:41 PM
 */
class Simi_Simiconnector_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('simiconnector/button.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return Mage::helper('adminhtml')->getUrl('simiconnector/rest/v2/storeviews/pwa');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'pwa_button',
                'label'     => $this->helper('adminhtml')->__('Sync'),
                'onclick'   => 'javascript:check(); return false;'
            ));

        return $button->toHtml();
    }
}