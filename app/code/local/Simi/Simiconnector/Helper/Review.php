<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 8/18/16
 * Time: 9:09 AM
 */
class Simi_Simiconnector_Helper_Review extends Mage_Core_Helper_Abstract
{
    public function getTotalRate($rates)
    {
        $total = $rates[0] * 1 + $rates[1] * 2 + $rates[2] * 3 + $rates[3] * 4 + $rates[4] * 5;
        return $total;
    }

    public function getAvgRate($rates, $total)
    {
        if ($rates[5] != 0)
            $avg = $total / $rates[5];
        else
            $avg = 0;
        return $avg;
    }

    function getRatingStar($productId)
    {
        $reviews = Mage::getModel('review/review')
            ->getResourceCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addEntityFilter('product', $productId)
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();
        /**
         * Getting numbers ratings/reviews
         */
        $star = array();
        $star[0] = 0;
        $star[1] = 0;
        $star[2] = 0;
        $star[3] = 0;
        $star[4] = 0;
        $star[5] = 0;
        if (count($reviews) > 0) {
            foreach ($reviews->getItems() as $review) {
                $star[5]++;
                $y = 0;
                foreach ($review->getRatingVotes() as $vote) {
                    $y += ($vote->getPercent() / 20);
                }
                $x = (int)($y / count($review->getRatingVotes()));
                $z = $y % 3;
                $x = $z < 5 ? $x : $x + 1;
                if ($x == 1) {
                    $star[0]++;
                } elseif ($x == 2) {
                    $star[1]++;
                } elseif ($x == 3) {
                    $star[2]++;
                } elseif ($x == 4) {
                    $star[3]++;
                } elseif ($x == 5) {
                    $star[4]++;
                } elseif ($x == 0) {
                    $star[5]--;
                }
            }
        }
        return $star;
    }

    public function getReviews($productId)
    {
        $storeId = Mage::app()->getStore()->getId();
        $reviews = Mage::getModel('review/review')
            ->getResourceCollection()
            ->addStoreFilter($storeId)
            ->addEntityFilter('product', $productId)
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();

        return $reviews;
    }

    public function getReview($review_id)
    {
        return Mage::getModel('review/review')->load($review_id);
    }

    public function getReviewToAdd()
    {
        $block = Mage::getBlockSingleton('review/form');
        $is_allow = $block->getAllowWriteReviewFlag();
        if ($is_allow) {
            $info = array();
            $rates = array();
            if ($block->getRatings() && $block->getRatings()->getSize()) {
                foreach ($block->getRatings() as $_rating) {
                    $_options = array();
                    foreach ($_rating->getOptions() as $_option) {
                        $_options[] = array(
                            'key' => $_rating->getId(),
                            'value' => $_option->getId(),
                        );
                    }
                    $rates[] = array(
                        'rate_code' => $block->escapeHtml($_rating->getRatingCode()),
                        'rate_options' => $_options,
                    );
                }
            }
            $info[] = array('rates' => $rates, 'form_review' => array(
                'key_1' => 'nickname',
                'key_2' => 'title',
                'key_3' => 'detail',
                 'form_key'=> array(
                     array(
                         'key' => 'nickname',
                         'value' => 'Nickname'
                     ),
                     array(
                         'key' => 'title',
                         'value' => 'Title'
                     ),
                     array(
                         'key' => 'detail',
                         'value' => 'Detail'
                     ),
                )),
            );
            return $info;
        } else {
            return array($block->__('Only registered users can write reviews'));
        }
    }

    protected function _initProduct($product_id)
    {
        return Mage::getModel('catalog/product')->load($product_id);
    }

    public function saveReview($data)
    {
        $allowGuest = Mage::helper('review')->getIsGuestAllowToWrite();
        if (!$allowGuest) {
            throw new Exception(Mage::helper('review')->__('Guest can not write'), 4);
        }
        if (($product = $this->_initProduct($data['product_id'])) && !empty($data)) {
            $rating = $data['ratings'];
            $session = Mage::getSingleton('core/session');
            /* @var $session Mage_Core_Model_Session */
            $review = Mage::getModel('review/review')->setData($data);
            /* @var $review Mage_Review_Model_Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->setStores(array(Mage::app()->getStore()->getId()))
                        ->save();
                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                            ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    return array(
                        'review' => $review,
                        'message' => Mage::helper('review')->__('Your review has been accepted for moderation.'));
                } catch (Exception $e) {
                    throw new Exception(Mage::helper('review')->__('Unable to post the review'), 4);
                }
            } else {
                throw new Exception(Mage::helper('review')->__('Unable to post the review'), 4);
            }
        } else {
            throw new Exception(Mage::helper('review')->__('Invalid method.'), 4);
        }

    }
}