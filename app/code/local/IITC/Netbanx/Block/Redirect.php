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

class IITC_Netbanx_Block_Redirect extends Mage_Core_Block_Abstract {

  protected function _toHtml() {
    // Make sure there's actually an order first..
    if ((!is_object($this->getOrder())) || (!is_object($this->getOrder()->getBillingAddress())))
      return "<h3>Please refresh the page</h3>";

    // Construct the redirection form
    $form = new Varien_Data_Form();
    $form->setAction($this->payment_url())
      ->Setid('netbanx_iframe')
      ->setName('netbanx_iframe')
      ->setMethod('POST')
      ->setUseContainer(true);
    $form->addField("clickhere", 'submit', array('name'=>"clickhere", 'value' => "click here", 'label'=>'If it\'s taking too long'));

    // Add all the NETBANX parameters
    foreach ($this->craft_parameters() as $name => $value)
      $form->addField($name, 'hidden', array('name' => $name, 'value' => $value));
	
    // Render the page
    $html  = '<html><body>' . $this->__('Please wait whilst you\'re taken to the secure NETBANX page.') . '<br><br>';
    $html .= $form->toHtml();  
    $html .= '<script type="text/javascript">document.getElementById("netbanx_iframe").submit();</script>';
    $html .= '</body></html>';
    
    return $html;
  }
    
  function payment_url() {
    if (Mage::getStoreConfig('payment/Netbanx/test_mode'))
      $url = 'https://pay.test.netbanx.com/';
    else
      $url = 'https://pay.netbanx.com/';
    
    return $url . Mage::getStoreConfig('payment/Netbanx/merchant_name');
  }
    
  function craft_parameters() {
    $order = $this->getOrder();
    $billing = $order->getBillingAddress();
    
    $postcode = $billing->getPostcode();
    if (!$postcode)
      $postcode = 'NONE';
      
    $amount = sprintf('%0.2f', $order->getGrandTotal());
    if (Mage::getStoreConfig('payment/Netbanx/payment_amount_format_isminor'))
      $amount = preg_replace('/[^\d]+/', '', $amount);

    $parameters = array('nbx_payment_amount' => $amount,
			'nbx_currency_code' => $order->getOrderCurrencyCode(),
			'nbx_merchant_reference' => $order->getRealOrderId(),
			
			'nbx_email' => $order->getCustomerEmail(),
			'nbx_cardholder_name' => $billing->getName(),
			'nbx_houseno' => implode(',', $billing->getStreet()),
			'nbx_postcode' => $postcode,
			  
			'nbx_success_url' => $this->getURL('netbanx/payment/callback'),
			'nbx_failure_url' => $this->getURL('netbanx/payment/callback'),
			'nbx_return_url' => $this->getURL('netbanx/payment/return')
			);

    if (Mage::getStoreConfig('payment/Netbanx/redirect_enabled')) {
      $parameters['nbx_success_redirect_url'] = $this->getURL('netbanx/payment/success');
      $parameters['nbx_failure_redirect_url'] = $this->getURL('netbanx/payment/failure');
    }

    $secret_key = Mage::getStoreConfig('payment/Netbanx/secret_key');
    if ($secret_key)
      $parameters['nbx_checksum'] = sha1($parameters['nbx_payment_amount'].$parameters['nbx_currency_code'].$parameters['nbx_merchant_reference'].$secret_key);
      
    return $parameters;
  }
}
?>
