About
-----

This is a payment module for the Magento shopping cart which allows you to process transactions through the NETBANX payment gateway.

The module was originally released commercially with IITC Ltd. but as the company is stopping trading and NETBANX released their own module I have decided to open source it.

You can still [view the original page](http://iitc.info/products/magento-netbanx) for now although any new information is going to be either left here or [on my blog](http://kzar.co.uk/blog/view/netbanx-magento-payment-module).

Installation
------------
- Extract the archive you downloaded into your Magento directory
- Log into the Magento admin system and refresh the cache.
(Click System -> Cache Management -> Select Refresh in the drop down menu, make sure all the checkboxes are ticked and then select Save cache settings.)
- Finally you need to configure the module. To access the options click System -> Configuration -> Payment Methods (Under sales on the left hand side) -> NETBANX.
Now make sure the Merchant name and other settings are correct before pressing the 'Save Config' button.

Usage
-----

- The NETBANX payment option should now be visible to your customers, orders using NETBANX have notes saved giving details of payments / any problems. When you first install the module I recommend performing tests and checking these details to make sure everything is working correctly.

- If you want to display the NETBANX reference in your "Thank you" emails include this block; `{{block type="Netbanx/lookup" order_id=$order.increment_id}}`

Notes
-----

- Orders are updated when the NETBANX server performs a 'call back' reply to your system. There is sometimes a delay between the order being processed and this call back, if orders are taking a long time to complete ask NETBANX to perform call backs immediately for your integration.

- Due to a Magento bug redirection will only function properly if NETBANX does not return any URL parameters. Before enabling this feature contact them and make sure that is the case. Otherwise customers might be redirected to the wrong page when returning from NETBANX.

Support
-------

For commercial support email Dave, kzar@kzar.co.uk .

License
-------

Copyright 2009 Dave Barker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <[http://www.gnu.org/licenses/](http://www.gnu.org/licenses/)>.