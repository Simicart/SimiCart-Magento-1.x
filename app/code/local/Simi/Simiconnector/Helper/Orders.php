<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 11/4/16
 * Time: 4:05 PM
 */
class Simi_Simiconnector_Helper_Orders extends Mage_Core_Helper_Abstract
{
    public function invoiceOrder($order)
    {
        //1 success, 2 fail.
        $result = array(
            'status_updated' => 1,
            'message' => "",
        );
        if ($order->canInvoice()) {
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            if ($invoice->getTotalQty()) {
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();
                $order->setIsInProcess(true);
                $order->addStatusHistoryComment('Invoice was created from Mobile Tracking.', false);
                $order->save();
                $result['message'] = $this->__('Invoice was created from Mobile Tracking.');
            } else {
                $order->addStatusHistoryComment('Cannot create an invoice without products.', false);
                $order->save();
                $result['status_updated'] = 2;
                $result['message'] = $this->__('Cannot create an invoice without products.');
            }
        } else {
            $order->addStatusHistoryComment('Order cannot be invoiced.', false);
            $order->save();
            $result['status_updated'] = 2;
            $result['message'] = $this->__('Order cannot be invoiced.');
        }
        return $result;
    }

    //$params->is_mail - send email to customer.
    public function shipOrder($order, $params)
    {
        //1 success, 2 fail.
        $result = array(
            'status_updated' => 1,
            'message' => "",
        );
        //pending.tracking
        if ($order->canShip()) {
            $shipment = new Mage_Sales_Model_Order_Shipment_Api();
            $shipmentId = $shipment->create($order->getIncrementId());
            // mail customer
            if ($params->is_mail == 1) {
                $shipment->sendInfo($shipmentId);
            }
            $result['message'] = $this->__('The shipment has been created.');
        } else {
            $result['status_updated'] = 2;
            $result['message'] = $this->__('The shipment cannot be created');
        }
        return $result;
    }

    public function cancelOrder($order)
    {
        //1 success, 2 fail.
        $result = array(
            'status_updated' => 1,
            'message' => "",
        );
        if ($order->canCancel()) {
            $order->cancel();
            $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
            $order->addStatusHistoryComment('The order was canceled via Mobile Tracking', false);
            $order->save();
            $result['message'] = $this->__('The order was canceled via Mobile Tracking');
        } else {
            $result['status_updated'] = 2;
            $result['message'] = $this->__('The order cannot be canceled');
        }
        return $result;
    }

    public function holdOrder($order)
    {
        //1 success, 2 fail.
        $result = array(
            'status_updated' => 1,
            'message' => "",
        );
        if ($order->canHold()) {
            $order->hold();
            $order->addStatusHistoryComment('The order was holded via Mobile Tracking', false);
            $order->save();
            $result['message'] = $this->__('The order was holded via Mobile Tracking');
        } else {
            $order->addStatusHistoryComment('The order cannot be holded', false);
            $order->save();
            $result['status_updated'] = 2;
            $result['message'] = $this->__('The order cannot be holded');
        }
        return $result;
    }

    public function unHoldOrder($order){
        //1 success, 2 fail.
        $result = array(
            'status_updated' => 1,
            'message' => "",
        );
        if ($order->canUnhold()) {
            $order->unhold();
            $order->addStatusHistoryComment('The order was unholded via Mobile Tracking', false);
            $order->save();
            $result['message'] = $this->__('The order was unholded via Mobile Tracking');
        } else {
            $order->addStatusHistoryComment('The order cannot be unholded', false);
            $order->save();
            $result['status_updated'] = 2;
            $result['message'] = $this->__('The order cannot be unholded');
        }

        return $result;
    }
}