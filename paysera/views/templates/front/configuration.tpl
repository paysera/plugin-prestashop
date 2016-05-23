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
<img src="/modules/paysera/img/logo-lt.png" style="float:left; margin-right:15px;">
<b>{l s='This module allows you to accept payments by Paysera.' mod='paysera'}</b>
<br />
{l s='If the client chooses this payment mode, the order will change its status into a \'Waiting for payment\' status.' mod='paysera'}
<div style="clear: both;"></div>
<br />

{if $messages ne ''}
	{foreach from=$messages item=message}
	<div class="{$message.class|escape:'quotes'}">
		<img src="{$message.src|escape:'quotes'}" alt="#" />
		{$message.msg|escape:'quotes'}
	</div>
	{/foreach}
{/if}

<form action="{$requestUrl|escape:'quotes'}" method="post">
	<fieldset>
		<legend>{l s='Payment module details' mod='paysera'}</legend>
		<table id="form">
			<tr>
				<td colspan="2">{l s='Please specify the Paysera details' mod='paysera'}<br /><br /></td>
			</tr>
			<tr>
				<td width="200" style="height: 35px;">{l s='Paysera project ID:' mod='paysera'}</td>
				<td><input type="text" name="project_id" value="{$projectId|escape:'intval'}" style="width: 300px;" /></td>
			</tr>
			<tr>
				<td width="200" style="height: 35px;">{l s='Signature password:' mod='paysera'}</td>
				<td><input type="text" name="signature" value="{$projectPass|escape:'quotes'}" style="width: 300px;" /></td></tr>
			<tr>
				<td width="200" style="height: 35px;">{l s='Test mode:' mod='paysera'}</td>
				<td>
					<input type="radio" name="sandbox" value="1" {$sandboxOn|escape:'html'} />{l s='Yes' mod='paysera'}
					<input type="radio" name="sandbox" value="0" {$sandboxOff|escape:'html'} />{l s='No' mod='paysera'}
				</td>
			</tr>
			<tr>
				<td width="200" style="height: 35px;">{l s='Display payments list:' mod='paysera'}</td>
				<td>
					<input type="radio" name="paymentList" value="1" {$payListOn|escape:'html'} />{l s='Yes' mod='paysera'}
					<input type="radio" name="paymentList" value="0" {$payListOff|escape:'html'} />{l s='No' mod='paysera'}
				</td>
			</tr>
			<tr>
				<td width="200" style="height: 35px;">{l s='Default payment country:' mod='paysera'}</td>
				<td>
					<select name="defaultCountry">
					{foreach from=$countries item=country}
        				{if $country->getCode() == $defaultCountry}
        				<option selected="selected" value="{$country->getCode()|escape:'quotes'}">{$country->getTitle()|escape:'quotes'}</option>
        				{else}
        				<option value="{$country->getCode()|escape:'quotes'}">{$country->getTitle()|escape:'quotes'}</option>
        				{/if}
        			{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input class="button" name="btnSubmit" value="{l s='Update settings' mod='paysera'}" type="submit" />
				</td>
			</tr>
		</table>
	</fieldset>
</form>
