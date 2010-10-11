<?php
/* This file is part of the Magento Netbanx payment module (iitc).             *
 * Copyright Dave Barker 2009                                                  *
 *                                                                             *
 * The Magento Netbanx payment module (iitc) is free software: you can         *
 * redistribute it and/or modify it under the terms of the GNU General Public  *
 * License as published by the Free Software Foundation, either version 3      *
 * of the License, or (at your option) any later version.                      *
 *                                                                             *
 * the Magento Netbanx payment module (iitc) is distributed in the hope        *
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the implied  *
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   *
 * GNU General Public License for more details.                                *
 *                                                                             *
 * You should have received a copy of the GNU General Public License           *
 * along with the Magento Netbanx payment module (iitc).                       *
 * If not, see <http://www.gnu.org/licenses/>.                                 */

class IITC_Netbanx_PaymentController extends Mage_Core_Controller_Front_Action {

  public function indexAction() {
    $this->loadLayout();
    $this->renderLayout();
  }		
 		
  public function redirectAction() {
    $session = Mage::getSingleton('checkout/session');
    $order = Mage::getModel('sales/order')->loadbyIncrementId($session->getLastRealOrderId());

    // Prevent ugly errors
    $data = $order->getData();
    if (empty($data))
      return '';

    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_HOLDED,
			       'Customer redirected to NETBANX payment page, awaiting payment confirmation from NETBANX',
			       true);
    $order->save();
    
    $this->getResponse()
      ->setBody($this->getLayout()
		->createBlock('Netbanx/redirect')
		->setOrder($order)
		->setSession($session)
		->toHtml());
  }

  public function lookupAction() {
    $this->getResponse()->setBody($this->getLayout()->createBlock('Netbanx/lookup')->toHtml());
  }

  public function callbackAction() {
    $this->getResponse()->setBody($this->getLayout()->createBlock('Netbanx/callback')->toHtml());
  }

  public function successAction() {
    $session = Mage::getSingleton('checkout/session');
    $order = Mage::getModel('sales/order')->loadbyIncrementId($session->getLastRealOrderId());

    // Log access if order details are there
    $data = $order->getData();
    if (!empty($data)) {
      $order->addStatusToHistory($order->getStatus(),
				 'Customer redirected to Success page, waiting payment confirmation from NETBANX.',
				 false);
      $order->save();
    }

    // Clear shopping cart 
    $quote = $session->getQuote();
    $quote->setIsActive(false);
    $quote->delete();

    $this->loadLayout();
    $this->renderLayout();
  }

  public function failureAction() {
    $this->loadLayout();
    $this->renderLayout();
  }
  public function returnAction() {
    $session = Mage::getSingleton('checkout/session');
    $order = Mage::getModel('sales/order')->loadbyIncrementId($session->getLastRealOrderId());

    // Log access if order details are there
    $data = $order->getData();
    if (!empty($data)) {
      $order->addStatusToHistory($order->getStatus(),
				 'Customer clicked "Back to Merchant" link on NETBANX page.',
				 false);
      $order->save();
    }

    $this->loadLayout();
    $this->renderLayout();
  }
}
?>