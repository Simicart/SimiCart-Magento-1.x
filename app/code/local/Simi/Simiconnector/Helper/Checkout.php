<?php

/**

 */
class Simi_Simiconnector_Helper_Checkout extends Mage_Core_Helper_Abstract {
    /*
     * Product Options
     */

    public function convertOptionsCart($options) {
        $data = array();
        foreach ($options as $option) {
            $data[] = array(
                'option_title' => $option['label'],
                'option_value' => $option['value'],
                'option_price' => isset($option['price']) == true ? $option['price'] : 0,
            );
        }
        return $data;
    }

    public function getBundleOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item) {
        $options = array();
        $product = $item->getProduct();
        /**
         * @var Mage_Bundle_Model_Product_Type
         */
        $typeInstance = $product->getTypeInstance(true);

        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = $optionsQuoteItemOption ? unserialize($optionsQuoteItemOption->getValue()) : array();
        if ($bundleOptionsIds) {
            /**
             * @var Mage_Bundle_Model_Mysql4_Option_Collection
             */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

            $selectionsCollection = $typeInstance->getSelectionsByIds(
                    unserialize($selectionsQuoteItemOption->getValue()), $product
            );

            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections()) {


                    $bundleSelections = $bundleOption->getSelections();

                    foreach ($bundleSelections as $bundleSelection) {
                        $check = array();
                        $qty = Mage::helper('bundle/catalog_product_configuration')->getSelectionQty($product, $bundleSelection->getSelectionId()) * 1;
                        if ($qty) {
                            $check[] = $qty . ' x ' . $this->escapeHtml($bundleSelection->getName());
                            $option = array(
                                'option_title' => $bundleOption->getTitle(),
                                'option_value' => $qty . ' x ' . $this->escapeHtml($bundleSelection->getName()),
                                'option_price' => Mage::helper('core')->currency(Mage::helper('bundle/catalog_product_configuration')->getSelectionFinalPrice($item, $bundleSelection), false),
                            );
                        }
                    }
                    if ($check)
                        $options[] = $option;
                }
            }
        }

        return $options;
    }

    /**
     * Retrieves product options list
     *
     * @param Mage_Catalog_Model_Product_Configuration_Item_Interface $item
     * @return array
     */
    public function getOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item) {
        return array_merge(
                $this->getBundleOptions($item), $this->convertOptionsCart(Mage::helper('catalog/product_configuration')->getCustomOptions($item))
        );
    }

    /**
     *  for magento < 1.5.0.0
     * @param Mage_Sales_Model_Quote_Item $item
     * @return type
     */
    public function getUsedProductOption(Mage_Sales_Model_Quote_Item $item) {
        $typeId = $item->getProduct()->getTypeId();
        switch ($typeId) {
            case Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE:
                return $this->getConfigurableOptions($item);
                break;
            case Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE:
                return $this->getGroupedOptions($item);
                break;
        }

        return $this->getCustomOptions($item);
    }

    public function getConfigurableOptions(Mage_Sales_Model_Quote_Item $item) {
        $product = $item->getProduct();

        $attributes = $product->getTypeInstance(true)
                ->getSelectedAttributesInfo($product);
        $options = array_merge($attributes, $this->getCustomOptions($item));
        return $this->convertOptionsCart($options);
    }

    public function getGroupedOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item) {
        return;
    }

    public function getCustomOptions(Mage_Sales_Model_Quote_Item $item) {
        $options = array();
        $product = $item->getProduct();
        if ($optionIds = $item->getOptionByCode('option_ids')) {
            $options = array();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {

                    $quoteItemOption = $item->getOptionByCode('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                            ->setOption($option)
                            ->setQuoteItemOption($quoteItemOption);

                    $options[] = array(
                        'label' => $option->getTitle(),
                        'value' => $group->getFormattedOptionValue($quoteItemOption->getValue()),
                        'print_value' => $group->getPrintableOptionValue($quoteItemOption->getValue()),
                        'option_id' => $option->getId(),
                        'option_type' => $option->getType(),
                        'custom_view' => $group->isCustomizedView()
                    );
                }
            }
        }
        if ($addOptions = $item->getOptionByCode('additional_options')) {
            $options = array_merge($options, unserialize($addOptions->getValue()));
        }
        return $this->convertOptionsCart($options);
    }

    public function getCheckoutTermsAndConditions() {
        if (!Mage::getStoreConfig('simiconnector/terms_conditions/enable_terms'))
            return NULL;
        $data = array();
        $data['title'] = Mage::getStoreConfig('simiconnector/terms_conditions/term_title');
        $data['content'] = Mage::getStoreConfig('simiconnector/terms_conditions/term_html');
        return $data;
    }

    /*
     * Process order after
     */
    public function processOrderAfter($orderId,&$order){
        /*
         * save To App report
         */
        try {

            $newTransaction = Mage::getModel('simiconnector/appreport');
            $newTransaction->setOrderId($orderId);
            $newTransaction->save();
        } catch (Exception $exc) {

        }

        /*
         * App notification
         */
        if (Mage::getStoreConfig('simiconnector/notification/noti_purchase_enable')) {
            $categoryId = Mage::getStoreConfig('simiconnector/notification/noti_purchase_category_id');
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $categoryName = $category->getName();
            $categoryChildrenCount = $category->getChildrenCount();
            if ($categoryChildrenCount > 0)
                $categoryChildrenCount = 1;
            else
                $categoryChildrenCount = 0;

            $notification['show_popup'] = '1';
            $notification['title'] = Mage::getStoreConfig('simiconnector/notification/noti_purchase_title');
            $notification['url'] = Mage::getStoreConfig('simiconnector/notification/noti_purchase_url');
            $notification['message'] = Mage::getStoreConfig('simiconnector/notification/noti_purchase_message');
            $notification['notice_sanbox'] = 0;
            $notification['type'] = Mage::getStoreConfig('simiconnector/notification/noti_purchase_type');
            $notification['productID'] = Mage::getStoreConfig('simiconnector/notification/noti_purchase_product_id');
            $notification['categoryID'] = Mage::getStoreConfig('simiconnector/notification/noti_purchase_category_id');
            $notification['categoryName'] = $categoryName;
            $notification['has_children'] = $categoryChildrenCount;
            $notification['created_time'] = now();
            $notification['notice_type'] = 3;
            $order['notification'] = $notification;
        }
    }

}
