<?php

/**
 * Created by PhpStorm.
 * User: scott
 * Date: 5/28/18
 * Time: 5:20 PM
 */
class Simi_Simiconnector_Model_Api_Contacts extends Simi_Simiconnector_Model_Api_Abstract
{
    const XML_PATH_EMAIL_RECIPIENT = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE = 'contacts/email/email_template';
    const XML_PATH_ENABLED = 'contacts/contacts/enabled';

    public function setBuilderQuery()
    {

    }

    public function store()
    {
        $data = $this->getData();
        $params = $data['contents_array'];
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
        try {
            $dataObject = new Varien_Object();
            $dataObject->setData($params);
            $mailTemplate = Mage::getModel('core/email_template');
            /* @var $mailTemplate Mage_Core_Model_Email_Template */
            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                ->setReplyTo($params['email'])
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
                    null,
                    array('data' => $dataObject)
                );
            if (!$mailTemplate->getSentSuccess()) {
                throw new Exception(Mage::helper('simiconnector')->__('Something went wrong. Please try again!'));
            }

            $translate->setTranslateInline(true);
            return array(
                'success' => '1',
                'message' => Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.')
            );

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}