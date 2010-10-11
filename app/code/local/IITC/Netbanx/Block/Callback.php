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

class IITC_Netbanx_Block_Callback extends Mage_Core_Block_Abstract {		      

  protected function _toHtml() {
    $html = array('ok');

    $remote_ip = $this->getRequest()->getServer('REMOTE_ADDR');

    // Check it's NETBANX
    if (($remote_ip == '80.65.254.6') || ($remote_ip == '217.33.219.147')) {
      $html[] = 'security check passed';

      // Check for the parameters
      $params = $this->getRequest()->getParams();
      if ((empty($params)) || (!array_key_exists('nbx_merchant_reference', $params)))
	$html[] = 'No orderID given';
      // Find order
      else {
	$orderID = $params['nbx_merchant_reference']; 
	$order = Mage::getModel('sales/order')->loadbyIncrementId($orderID);	
	$order_data = $order->getData();

	if (empty($order_data)) {
	  $html[] = 'Order "' . $orderID . '" not found';
	}
	else {
	  // Check transaction status
	  if (!array_key_exists('nbx_status', $params))
	    $html[] = 'No status given';
	  else {
	    $order_status = $params['nbx_status'];
	    $html[] = 'Order "' . $orderID . '" status "' . $order_status . '"';
	      
	    $order->addStatusToHistory($order->getStatus(),
				       'Received background callback from NETBANX [' . $order_status . ']',
				       false);

	    // Pending transaction
	    if ($params['nbx_status'] == 'pending') {
	      $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_HOLDED,
					 'NETBANX transaction is pending, check payment status with them.',
					 true);
	      $html[] = 'transaction pending';
	    }
	      
	    // Successful transaction
	    else if (($order_status == 'passed') && (array_key_exists('nbx_netbanx_reference', $params)) && ($params['nbx_netbanx_reference'])) {
	      // Check amount
	      $amount = sprintf('%0.2f', $order->getGrandTotal());
	      if (Mage::getStoreConfig('payment/Netbanx/payment_amount_format_isminor'))
		$amount = preg_replace('/[^\d]+/', '', $amount);

	      // Amount doesn't match
	      if (!array_key_exists('nbx_payment_amount', $params))
		$html[] = 'no amount given';
	      else if ($amount != $params['nbx_payment_amount']) {
		$order->addStatusToHistory($order->getStatus(),
					   'ERROR NETBANX transaction amount "' . $params['nbx_payment_amount'] . '" doesn\'t match order amount "' . $amount . '", possible fraud attempt. Check this transaction with NETBANX.',
					   false);
		$html[] = 'amount wrong given "' . $params['nbx_payment_amount'] . '"'; //expected "' . $amount . '"';
	      }
	      // Amount is OK
	      else {
		// Perform checksum test if required
		$secret_key = Mage::getStoreConfig('payment/Netbanx/secret_key');
		if ($secret_key) {
		  if ((!array_key_exists('nbx_currency_code', $params))) {
		    $html[] = 'currency code missing so skipping checksum test';
		    $secret_key = '';
		  }
		  else {
		    $checksum = sha1($params['nbx_payment_amount'] . $params['nbx_currency_code'] . $params['nbx_merchant_reference'] . $params['nbx_netbanx_reference'] . $secret_key);
		  }
		}

		// Checksum missing
		if (($secret_key) && (!array_key_exists('nbx_checksum', $params))) {
		  $order->addStatusToHistory($order->getStatus(),
					     'ERROR Checksum was expected but it\'s missing, check secret key setting is correct. Possible fraud attempt. Check payment status with NETBANX.',
					     false);
		  $html[] = 'checksum expected but missing (check secret key)';
		}
		// Checksum didn't match
		else if (($secret_key) && ($checksum != $params['nbx_checksum'])) {
		  $order->addStatusToHistory($order->getStatus(),
					     'ERROR Checksum mismatch, check secret key setting is correct. Possible fraud attempt. Check payment status with NETBANX. "' . $params['nbx_payment_amount'] . '" "' . $params['nbx_currency_code'] . '" "' . $params['nbx_merchant_reference'] . '" "' . $params['nbx_netbanx_reference'] . '" "' . $secret_key . '" "' . $params['nbx_checksum'],
					     false);
		  $html[] = 'checksum mismatch (check secret key)';
		}
		// Checksum matched / not checked
		else {
		  if ($secret_key)
		    $html[] = 'checksum matched';
		  else
		    $html[] = 'checksum ignored';
		  
		  // Update everything, order is complete
		  $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE,
					     'NETBANX transaction successful, NETBANX Reference: ' . $params['nbx_netbanx_reference'],
					     true);
		  
                  // Save NETBANX reference so it's ready for the "thank you" email
                  $order->save();

		  $html[] = 'transaction success';

		  // Clear customer's shopping cart
		  $quote = Mage::getModel('sales/quote')->load($order->getQuoteID());
		  $quote->setIsActive(false);
		  $quote->delete();
		  
		  // Send confimation email(s)
		  $order->sendNewOrderEmail();
		  $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE,
					     'Customer confirmation email sent',
					     true);
		  $html[] = 'customer emailed';
		}
	      }
	    }
	    // Failed transaction
	    else {
	      $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED,
					 'NETBANX transaction failed, order cancelled.',
					 true);
	      $html[] = 'transaction failed';
	    }
	      
	    // Finish up
	    $order->save();
	  }
	}
      }
    }	
    else
      $html[] = 'security check failed';	
      
    return implode(',',$html);
  }
}
?>