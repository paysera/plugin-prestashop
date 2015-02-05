{*
* 2007-2014 PrestaShop
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
*}
<script type="text/javascript">
{literal}
	$(document).ready(function(){
		$('#paysera-form').submit();
	});
{/literal}
</script>
<style type="text/css">
    .col-sm-9 { width:100% !important; }
    .col-sm-3 { display:none !important; }
</style>
...

<form action="{$payUrl|escape:'quotes'}" method="POST" id="paysera-form">
	{foreach from=$request key=name item=value}
	<input type="hidden" name="{$name|escape:'quotes'}" value="{$value|escape:'quotes'}" />
	{/foreach}
<input type="submit" value="{l s='Continue >>' mod='paysera'}" />
</form>
