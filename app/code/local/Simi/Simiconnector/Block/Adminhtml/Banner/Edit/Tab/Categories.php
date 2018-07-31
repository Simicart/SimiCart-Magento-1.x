<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Category chooser for Wysiwyg CMS widget
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Simi_Simiconnector_Block_Adminhtml_Banner_Edit_Tab_Categories extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    protected $_selectedIds = array();
    protected $_checkRoot=false;

    protected function _prepareLayout()
    {
        $this->setTemplate('simiconnector/banner/categories.phtml');
    }
    public function getCategoryIds()
    {
        return $this->_selectedIds;
    }

    public function is_Root()
    {
        if($webId=Mage::helper('simiconnector/cloud')->getWebsiteIdSimiUser()){
            return $this->_checkRoot;
        }

        return $this->getRoot()->getChecked();
    }
    public function setCategoryIds($ids)
    {
        if (empty($ids)) {
            $ids = array();
        }
        elseif (!is_array($ids)) {
            $ids = array((int)$ids);
        }

        $this->_selectedIds = $ids;
        return $this;
    }

    /**
     * Get JSON of a tree node or an associative array
     *
     * @param Varien_Data_Tree_Node|array $node
     * @param int $level
     * @return string
     */
    protected function _getNodeJson($node, $level = 1)
    {
        $item = array();
        $item['text']= $this->htmlEscape($node->getName());

        if ($this->_withProductCount) {
             $item['text'].= ' ('.$node->getProductCount().')';
        }

        $item['id']  = $node->getId();
        $item['path'] = $node->getData('path');
        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        $item['allowDrop'] = false;
        $item['allowDrag'] = false;

        if ($node->hasChildren()) {
            $item['children'] = array();
            foreach ($node->getChildren() as $child) {
                $item['children'][] = $this->_getNodeJson($child, $level + 1);
            }
        }

        if (empty($item['children']) && (int)$node->getChildrenCount() > 0) {
            $item['children'] = array();
        }

        if (!empty($item['children'])) {
            $item['expanded'] = true;
        }

        if (in_array($node->getId(), $this->getCategoryIds())) {
            $item['checked'] = true;
        }

        return $item;
    }

    public function getRoot($parentNodeCategory=null, $recursionLevel=3)
    {
        if($webId=Mage::helper('simiconnector/cloud')->getWebsiteIdSimiUser()){
            $websiteModel = Mage::getModel('core/website')->load($webId);
            $group = Mage::getModel('core/store_group')->load($websiteModel->getData('default_group_id'));
            if($group->getId()) {
                $category = Mage::getModel('catalog/category')->load($group->getData('root_category_id'));
                if(in_array($group->getData('root_category_id'), $this->getCategoryIds())){
                    $this->_checkRoot = true;
                }

                return parent::getRoot($category);
            }
        }

        return parent::getRoot();
    }

    protected function _getDefaultStoreId()
    {
        if($webId=Mage::helper('simiconnector/cloud')->getWebsiteIdSimiUser()){
            return Mage::getModel('core/store')->getCollection()->addFieldToFilter('website_id', $webId)
                ->getFirstItem()->getId();
        }

        return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }

    public function getCategoryCollection()
    {
        $collection = parent::getCategoryCollection();

        if (Mage::getStoreConfig('simiconnector/general/categories_in_app'))
            $_visible_array = explode(',', Mage::getStoreConfig('simiconnector/general/categories_in_app'));
        $collection->addFieldToFilter('entity_id', array('in' => $_visible_array));

        return $collection;
    }
}
