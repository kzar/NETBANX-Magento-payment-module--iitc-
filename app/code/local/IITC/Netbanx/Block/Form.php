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

class IITC_Netbanx_Block_Form extends Mage_Payment_Block_Form {

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('netbanx/form.phtml');
  }
}
?>