<?php
/**
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    EVP International, JSC <plugins@paysera.com>
*  @copyright 2004-2014 EVP International, JSC
*  @license   http://opensource.org/licenses/GPL-3.0  GNU GENERAL PUBLIC LICENSE (GPL-3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class PayseraCancelModuleFrontController extends ModuleFrontController {
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		Tools::redirect('index.php?controller=order&step=1');
	}
}
