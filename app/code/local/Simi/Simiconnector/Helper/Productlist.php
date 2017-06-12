<?php

class Simi_Simiconnector_Helper_Productlist extends Mage_Core_Helper_Abstract
{

    public function getListTypeId()
    {
        return array(
            1 => Mage::helper('simiconnector')->__('Custom Product List'),
            2 => Mage::helper('simiconnector')->__('Best Seller'),
            3 => Mage::helper('simiconnector')->__('Most View'),
            4 => Mage::helper('simiconnector')->__('Newly Updated'),
            5 => Mage::helper('simiconnector')->__('Recently Added')
        );
    }

    public function getTypeOption()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Custom Product List')),
            array('value' => 2, 'label' => Mage::helper('simiconnector')->__('Best Seller')),
            array('value' => 3, 'label' => Mage::helper('simiconnector')->__('Most View')),
            array('value' => 4, 'label' => Mage::helper('simiconnector')->__('Newly Updated')),
            array('value' => 5, 'label' => Mage::helper('simiconnector')->__('Recently Added')),
        );
    }

    public function getProductCollection($listModel)
    {
        $storeId = Mage::app()->getStore()->getId();
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
                $collection->getSelect()->joinInner(array('e2' => 'catalog_product_flat_' . $storeId), 'e2.entity_id = e.entity_id');
                break;
            //Most Viewed
            case 3:
                $collection = Mage::getResourceModel('reports/product_collection')
                    ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                    ->addViewsCount()
                    ->addMinimalPrice()
                    ->addTaxPercents()
                    ->addStoreFilter();
                $collection->getSelect()->joinInner(array('e2' => 'catalog_product_flat_' . $storeId), 'e2.entity_id = e.entity_id');
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

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        return $collection;
    }

    /*
     * Matrix Helper Functions
     */

    public function getMatrixRowOptions()
    {
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

    public function getMatrixLayoutMockup($storeviewid)
    {
        $rows = array();
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('homecategory');
        $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
        $simicategoryCollection = Mage::getModel('simiconnector/simicategory')->getCollection()->setOrder('sort_order', 'desc')->addFieldToFilter('status', '1');;
        $simicategoryCollection->getSelect()
            ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.simicategory_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . $storeviewid);


        foreach ($simicategoryCollection as $simicat) {
            if (!$rows[$simicat->getData('matrix_row')])
                $rows[(int)$simicat->getData('matrix_row')] = array();

            $editUrl = Mage::helper("adminhtml")->getUrl('*/simiconnector_simicategory/edit', array('id' => $simicat->getId()));
            $title = '<a href="' . $editUrl . '" style="background-color:rgba(255,255,255,0.7); text-decoration:none; text-transform: uppercase; color: black">' . $simicat->getData('simicategory_name') . '</a>';

            $rows[(int)$simicat->getData('matrix_row')][] = array(
                'id' => $simicat->getId(),
                'image' => $simicat->getData('simicategory_filename'),
                'image_tablet' => $simicat->getData('simicategory_filename_tablet'),
                'matrix_width_percent' => $simicat->getData('matrix_width_percent'),
                'matrix_height_percent' => $simicat->getData('matrix_height_percent'),
                'matrix_width_percent_tablet' => $simicat->getData('matrix_width_percent_tablet'),
                'matrix_height_percent_tablet' => $simicat->getData('matrix_height_percent_tablet'),
                'title' => $title,
                'sort_order' => $simicat->getData('sort_order')
            );
        }

        $listtypeID = Mage::helper('simiconnector')->getVisibilityTypeId('productlist');
        $listCollection = Mage::getModel('simiconnector/productlist')->getCollection()->addFieldToFilter('list_status', '1');
        $listCollection->getSelect()
            ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.productlist_id AND visibility.content_type = ' . $listtypeID . ' AND visibility.store_view_id =' . $storeviewid);

        foreach ($listCollection as $productlist) {
            if (!$rows[$productlist->getData('matrix_row')])
                $rows[(int)$productlist->getData('matrix_row')] = array();

            $editUrl = Mage::helper("adminhtml")->getUrl('*/*/edit', array('id' => $productlist->getId()));
            $title = '<a href="' . $editUrl . '" style="background-color:rgba(255,255,255,0.7); text-decoration:none; text-transform: uppercase; color: black">' . $productlist->getData('list_title') . '  </a>';
            $rows[(int)$productlist->getData('matrix_row')][] = array(
                'id' => $productlist->getId(),
                'image' => $productlist->getData('list_image'),
                'image_tablet' => $productlist->getData('list_image_tablet'),
                'matrix_width_percent' => $productlist->getData('matrix_width_percent'),
                'matrix_height_percent' => $productlist->getData('matrix_height_percent'),
                'matrix_width_percent_tablet' => $productlist->getData('matrix_width_percent_tablet'),
                'matrix_height_percent_tablet' => $productlist->getData('matrix_height_percent_tablet'),
                'title' => $title,
                'sort_order' => $productlist->getData('sort_order')
            );
        }
        ksort($rows);
        try {
            foreach ($rows as $index => $row) {
                usort($row, function ($a, $b) {
                    return $a['sort_order'] - $b['sort_order'];
                });
                $rows[$index] = $row;
            }
        } catch (Exception $e) {

        }
        $html = '</br> <b> Matrix Theme Mockup Preview: </b></br>(Save Item to update your Changes)</br></br>';
        $html .= '<table><tr><td style="text-align:center">Phone Screen Mockup Preview: </br>';
        $html .= $this->drawMatrixMockupTable(170, 320, false, $rows);
        $html .= '</td><td> </td><td style="text-align:center">Tablet Screen Mockup Preview: </br>';
        $html .= $this->drawMatrixMockupTable(178, 512, true, $rows) . '</td></tr></table>';
        return $html;
    }

    public function drawMatrixMockupTable($bannerHeight, $bannerWidth, $is_tablet, $rows)
    {
        if (!$is_tablet) {
            $margin = 8;
            $screenHeight = 568;
            $topmargin = 30;
            $bottommargin = 70;
        } else {
            $margin = 25;
            $screenHeight = 384;
            $topmargin = 10;
            $bottommargin = 50;
        }
        //phone shape
        $html = '<div style="background-color:black; width:' . ($bannerWidth + $margin * 2) . 'px; height:' . ($screenHeight + $topmargin + $bottommargin) . 'px; border-radius: 30px;"><br>';
        //screen
        $html .= '<div style="background-color:white; width:' . $bannerWidth . 'px;margin :' . $margin . 'px; height:' . $screenHeight . 'px ;margin-top: ' . $topmargin . 'px ; overflow-y:scroll; overflow-x:hidden;">';
        //logo (navigation)
        $html .= '<span style="color:white ; font-size: 18px; line-height: 35px; margin: 0 0 24px;"> <div> <div style= "background-color:#FF6347; width:' . $bannerWidth . '; height:' . ($bannerHeight / 6) . 'px ; text-align:center; background-image:url(https://www.simicart.com/skin/frontend/default/simicart2.0/images/menu.jpg); background-repeat:no-repeat;background-size: ' . ($bannerHeight / 6) . 'px ' . ($bannerHeight / 6) . 'px; " ><b>APPLICATION LOGO</b></div></div>';
        //banner
        $html .= '<div style="background-color:#cccccc; height:' . $bannerHeight . 'px; width:' . $bannerWidth . 'px;"><br><br><b>BANNER AREA</b></div>';
        //categories and product lists
        foreach ($rows as $row) {
            $totalWidth = 0;
            $cells = '';
            foreach ($row as $rowItem) {

                if ($is_tablet) {
                    if ($rowItem['image_tablet'] != null)
                        $rowItem['image'] = $rowItem['image_tablet'];
                    if ($rowItem['matrix_width_percent_tablet'] != null)
                        $rowItem['matrix_width_percent'] = $rowItem['matrix_width_percent_tablet'];
                    if ($rowItem['matrix_height_percent_tablet'] != null)
                        $rowItem['matrix_height_percent'] = $rowItem['matrix_height_percent_tablet'];
                }

                $rowWidth = $rowItem['matrix_width_percent'] * $bannerWidth / 100;
                $rowHeight = $rowItem['matrix_height_percent'] * $bannerWidth / 100;
                $totalWidth += $rowWidth;

                $cells .= '<span style="display:inline-block;  width:' . $rowWidth . 'px; height: ' . $rowHeight . 'px;
                overflow:hidden; background-image:url(' . $rowItem['image'] . '); background-repeat:no-repeat;
                background-size: ' . $rowWidth . 'px ' . $rowHeight . 'px;">' . $rowItem['title'] . '</span>';
            }
            if ($totalWidth > $rowWidth)
                $style = 'overflow-x: scroll; overflow-y: hidden;';
            else
                $style = 'overflow: hidden;';
            $html .= '<div style="' . $style . 'width: ' . $bannerWidth . 'px"> <div style="width:' . $totalWidth . 'px; height:' . $rowHeight . 'px">' . $cells;
            $html .= '</div></div>';
        }
        $html .= '</span></div></div>';
        return $html;
    }

    public function autoFillMatrixRowHeight()
    {
        $rows = array();
        foreach (Mage::getModel('simiconnector/simicategory')->getCollection() as $simicat) {
            $currentIndex = $simicat->getData('matrix_row');
            if (!$rows[$currentIndex])
                $rows[$currentIndex] = array('phone' => $simicat->getData('matrix_height_percent'), 'tablet' => $simicat->getData('matrix_height_percent_tablet'));
        }
        foreach (Mage::getModel('simiconnector/productlist')->getCollection() as $productlist) {
            $currentIndex = $productlist->getData('matrix_row');
            if (!$rows[$currentIndex])
                $rows[$currentIndex] = array('phone' => $productlist->getData('matrix_height_percent'), 'tablet' => $productlist->getData('matrix_height_percent_tablet'));
        }
        ksort($rows);
        $script = '
            function autoFillHeight(row){
                var returnValue = 100;
                switch(row) {';
        foreach ($rows as $index => $row) {
            $script .= '  case "' . $index . '":
                        $("matrix_height_percent").value = "' . $row['phone'] . '";
                        $("matrix_height_percent_tablet").value = "' . $row['tablet'] . '";
                        break; ';
        }
        $script .= '}}
        ';
        return $script;
    }

    public function updateMatrixRowHeight($matrix_row, $matrix_height_percent, $matrix_height_percent_tablet)
    {
        foreach (Mage::getModel('simiconnector/productlist')->getCollection() as $productList) {
            if (($productList->getData('matrix_row') == $matrix_row) && (($productList->getData('matrix_height_percent') != $matrix_height_percent) || ($productList->getData('matrix_height_percent_tablet') != $matrix_height_percent_tablet))) {
                $productList->setData('matrix_height_percent', $matrix_height_percent);
                $productList->setData('matrix_height_percent_tablet', $matrix_height_percent_tablet);
                $productList->save();
            }
        }
        foreach (Mage::getModel('simiconnector/simicategory')->getCollection() as $homecategory) {
            if (($homecategory->getData('matrix_row') == $matrix_row) && (($homecategory->getData('matrix_height_percent') != $matrix_height_percent) || ($homecategory->getData('matrix_height_percent_tablet') != $matrix_height_percent_tablet))) {
                $homecategory->setData('matrix_height_percent', $matrix_height_percent);
                $homecategory->setData('matrix_height_percent_tablet', $matrix_height_percent_tablet);
                $homecategory->save();
            }
        }
    }

}
