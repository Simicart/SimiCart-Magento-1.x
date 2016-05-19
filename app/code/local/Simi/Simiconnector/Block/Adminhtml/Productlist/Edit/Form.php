<?php

/**

 */
class Simi_Simiconnector_Block_Adminhtml_Productlist_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare form's information for block
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Edit_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array(
                'id' => $this->getRequest()->getParam('id'),
            )),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
