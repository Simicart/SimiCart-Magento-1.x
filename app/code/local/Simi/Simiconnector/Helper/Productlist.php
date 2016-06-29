<?php

class Simi_Simiconnector_Helper_Productlist extends Mage_Core_Helper_Abstract {

    public function getListTypeId() {
        return array(
            1 => Mage::helper('simiconnector')->__('Custom Product List'),
            2 => Mage::helper('simiconnector')->__('Best Seller'),
            3 => Mage::helper('simiconnector')->__('Most View'),
            4 => Mage::helper('simiconnector')->__('Newly Updated'),
            5 => Mage::helper('simiconnector')->__('Recently Added')
        );
    }

    public function getTypeOption() {
        return array(
            array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Custom Product List')),
            array('value' => 2, 'label' => Mage::helper('simiconnector')->__('Best Seller')),
            array('value' => 3, 'label' => Mage::helper('simiconnector')->__('Most View')),
            array('value' => 4, 'label' => Mage::helper('simiconnector')->__('Newly Updated')),
            array('value' => 5, 'label' => Mage::helper('simiconnector')->__('Recently Added')),
        );
    }

    public function getProductCollection($listModel) {
        $collection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')
                        ->getProductAttributes())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addUrlRewrite();
        switch ($listModel->getData('list_type')) {
            //Product List
            case 1:
                $collection->addFieldToFilter('entity_id', array('in' => explode(',', $listModel->getData('list_products'))));
                break;
            //Best seller
            case 2:
                $collection = Mage::getResourceModel('reports/product_collection')
                        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                        ->addOrderedQty()->addMinimalPrice()
                        ->addTaxPercents()
                        ->addStoreFilter()
                        ->setOrder('ordered_qty', 'desc');
                break;
            //Most Viewed
            case 3:
                $collection = Mage::getResourceModel('reports/product_collection')
                        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                        ->addViewsCount()
                        ->addMinimalPrice()
                        ->addTaxPercents()
                        ->addStoreFilter();
                break;
            //New Updated
            case 4:
                $collection->setOrder('updated_at', 'desc');
                break;
            //Recently Added
            case 5:
                $collection->setOrder('created_at', 'desc');
                break;
            default:
                break;
        }
        return $collection;
    }

    public function getMatrixRowOptions() {
        $rows = array();
        $highestRow = 0;
        foreach (Mage::getModel('simiconnector/simicategory')->getCollection() as $simicat) {
            $currentIndex = $simicat->getData('matrix_row');
            if (!$rows[$currentIndex])
                $rows[$currentIndex] = array();
            if ($currentIndex >= $highestRow)
                $highestRow = $currentIndex + 1;
            $rows[$currentIndex][] = $simicat->getData('simicategory_name');
        }
        foreach (Mage::getModel('simiconnector/productlist')->getCollection() as $productlist) {
            $currentIndex = $productlist->getData('matrix_row');
            if (!$rows[$currentIndex])
                $rows[$currentIndex] = array();
            if ($currentIndex >= $highestRow)
                $highestRow = $currentIndex + 1;
            $rows[$currentIndex][] = $productlist->getData('list_title');
        }
        ksort($rows);
        $returnArray = array(array('value' => $highestRow, 'label' => 'Create New Row'));
        foreach ($rows as $index => $row)
            $returnArray[] = array('value' => $index, 'label' => $index . '-' . implode(',', $row));
        return $returnArray;
    }

    public function getMatrixLayoutMockup($storeviewid) {
        $rows = array();
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('homecategory');
        $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
        $simicategoryCollection = Mage::getModel('simiconnector/simicategory')->getCollection()->setOrder('sort_order', 'desc');
        $simicategoryCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.simicategory_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . $storeviewid);


        foreach ($simicategoryCollection as $simicat) {
            if (!$rows[$simicat->getData('matrix_row')])
                $rows[(int) $simicat->getData('matrix_row')] = array();

            $title = '<b>' . $simicat->getData('simicategory_name') . '</b> <font size="1">' .
                    '<br> Type: Home Category' .
                    '<br> Row: ' . $simicat->getData('matrix_row') . '</font>';

            $rows[(int) $simicat->getData('matrix_row')][] = array(
                'id' => $simicat->getId(),
                'image' => $simicat->getData('simicategory_filename'),
                'image_tablet' => $simicat->getData('simicategory_filename_tablet'),
                'matrix_width_percent' => $simicat->getData('matrix_width_percent'),
                'matrix_height_percent' => $simicat->getData('matrix_height_percent'),
                'title' => $title,
            );
        }

        $listtypeID = Mage::helper('simiconnector')->getVisibilityTypeId('productlist');
        $listCollection = Mage::getModel('simiconnector/productlist')->getCollection();
        $listCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.productlist_id AND visibility.content_type = ' . $listtypeID . ' AND visibility.store_view_id =' . $storeviewid);

        foreach ($listCollection as $productlist) {
            if (!$rows[$productlist->getData('matrix_row')])
                $rows[(int) $productlist->getData('matrix_row')] = array();

            $title = '<b>' . $productlist->getData('list_title') . '</b> <font size="1">' .
                    '<br> Type: Product List' .
                    '<br> Row: ' . $productlist->getData('matrix_row') . '</font>';

            $rows[(int) $productlist->getData('matrix_row')][] = array(
                'id' => $productlist->getId(),
                'image' => $productlist->getData('list_image'),
                'image_tablet' => $productlist->getData('list_image_tablet'),
                'matrix_width_percent' => $productlist->getData('matrix_width_percent'),
                'matrix_height_percent' => $productlist->getData('matrix_height_percent'),
                'title' => $title,
            );
        }
        ksort($rows);
        
        $bannerHeight = 170;
        $bannerWidth = 320;
        $bannertypeID = Mage::helper('simiconnector')->getVisibilityTypeId('banner');
        $bannerCollection = Mage::getModel('simiconnector/banner')->getCollection();
        $bannerCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.banner_id AND visibility.content_type = ' . $bannertypeID . ' AND visibility.store_view_id =' . $storeviewid)
        ;
        foreach ($bannerCollection as $banner) {
            $bannerModel = $banner;
            break;
        }
        $html = '</br> Mockup Preview: </br></br> <span style="-webkit-text-fill-color: white; text-shadow: 1px 1px #111111;">';
        $html.= '<table><tr height="' . $bannerHeight . '"><td style="background-image:url(' . $bannerModel->getData('banner_name') . '); background-repeat:no-repeat;
                 background-size: ' . $bannerWidth . 'px ' . $bannerHeight . 'px;" width="' . $bannerWidth . 'px"><b>Banner</b></td></tr></table>';
        foreach ($rows as $row) {
            $totalWidth = 0;
            $totalHeight = 0;
            $cells = '';
            foreach ($row as $rowItem) {
                $rowWidth = $rowItem['matrix_width_percent'] * $bannerWidth / 100;
                $rowHeight = $rowItem['matrix_height_percent'] * $bannerHeight / 100;
                $totalWidth += $rowWidth;
                if ($totalHeight < $rowHeight)
                    $totalHeight = $rowHeight;
                $cells .= '<td style="background-image:url(' . $rowItem['image'] . ');
                background-repeat:no-repeat;
                 background-size: ' . $rowWidth . 'px ' . $rowHeight . 'px;" width="' . $rowWidth . 'px">
                <div style="height: ' . $rowHeight . 'px; overflow:hidden;">' . $rowItem['title'] . '</div></td>';
            }
            $html.= '<table width="' . $totalWidth . 'px"><tr height="' . $totalHeight . 'px">' . $cells;
            $html.= '</tr></table>';
        }
        $html . '</span>';
        return $html;
    }

    public function autoFillMatrixRowHeight() {
        $rows = array();
        foreach (Mage::getModel('simiconnector/simicategory')->getCollection() as $simicat) {
            $currentIndex = $simicat->getData('matrix_row');
            if (!$rows[$currentIndex])
                $rows[$currentIndex] = $simicat->getData('matrix_height_percent');
        }
        foreach (Mage::getModel('simiconnector/productlist')->getCollection() as $productlist) {
            $currentIndex = $productlist->getData('matrix_row');
            if (!$rows[$currentIndex])
                $rows[$currentIndex] = $productlist->getData('matrix_height_percent');
        }
        ksort($rows);
        $script = '
            function autoFillHeight(row){
                var returnValue = 100;
                switch(row) {';
        foreach ($rows as $index => $row) {
            $script .= '  case "' . $index . '":
                        $("matrix_height_percent").value = ' . $row . ';
                        break; ';
        }
        $script .= '}}
        ';
        return $script;
    }

    public function updateMatrixRowHeight($matrix_row, $matrix_height_percent) {
        foreach (Mage::getModel('simiconnector/productlist')->getCollection() as $productList) {
            if (($productList->getData('matrix_row') == $matrix_row) && ($productList->getData('matrix_height_percent') != $matrix_height_percent)) {
                $productList->setData('matrix_height_percent', $matrix_height_percent);
                $productList->save();
            }
        }
        foreach (Mage::getModel('simiconnector/simicategory')->getCollection() as $homecategory) {
            if (($homecategory->getData('matrix_row') == $matrix_row) && ($homecategory->getData('matrix_height_percent') != $matrix_height_percent)) {
                $homecategory->setData('matrix_height_percent', $matrix_height_percent);
                $homecategory->save();
            }
        }
    }

}
