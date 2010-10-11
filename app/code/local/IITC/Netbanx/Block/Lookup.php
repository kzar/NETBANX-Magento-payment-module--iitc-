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

// Example useage:
// {{block type="Netbanx/lookup" order_id=$order.increment_id}}

class IITC_Netbanx_Block_Lookup extends Mage_Core_Block_Abstract {
  protected function _toHtml() {
    if ($this->getOrderId())
      return $this->netbanxRef($this->getOrderId());
  }

  function netbanxRef($orderNumber) {
    $db = Mage::getSingleton('core/resource')->getConnection('core_read');

    $orderNumber = $db->quote($orderNumber);
    $SQL = "select value from sales_order_entity_text where entity_id in (select entity_id from sales_order_entity where parent_id=(select entity_id from sales_order where increment_id=" . $orderNumber . ")) and value like \"%Reference:%\"";

    $data = $db->fetchAll($SQL);

    if ($data && array_key_exists(0, $data) && array_key_exists('value', $data[0])) {
      $data = $data[0]['value'];
      $data = explode(": ", $data);

      if (count($data) > 1)
        return $data[1];
    }
    return "";
  }
}
?>