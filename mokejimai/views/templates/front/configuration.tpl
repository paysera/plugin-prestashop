<img src="/modules/mokejimai/img/logo-lt.png" style="float:left; margin-right:15px;">
<b>{l s='This module allows you to accept payments by mokejimai.lt.' mod='mokejimai'}</b>
<br />
{l s='If the client chooses this payment mode, the order will change its status into a \'Waiting for payment\' status.' mod='mokejimai'}
<div style="clear: both;"></div>
<br />

{if $messages ne ''}
	{foreach from=$messages item=message}
	<div class="{$message.class}">
		<img src="{$message.src}" alt="#" />
		{$message.msg}
	</div>
	{/foreach}
{/if}

<form action="{$requestUrl}" method="post">
	<fieldset>
		<legend>{l s='Payment module details' mod='mokejimai'}</legend>
		<table id="form">
			<tr>
				<td colspan="2">{l s='Please specify the mokejimai.lt details' mod='mokejimai'}<br /><br /></td>
			</tr>
			<tr>
				<td width="200" style="height: 35px;">{l s='Paysera.com project ID:' mod='mokejimai'}</td>
				<td><input type="text" name="project_id" value="{$projectId}" style="width: 300px;" /></td>
			</tr>
			<tr>
				<td width="200" style="height: 35px;">{l s='Signature password:' mod='mokejimai'}</td>
				<td><input type="text" name="signature" value="{$projectPass}" style="width: 300px;" /></td></tr>
			<tr>
				<td width="200" style="height: 35px;">{l s='Test mode:' mod='mokejimai'}</td>
				<td>
					<input type="radio" name="sandbox" value="1" {$sandboxOn} />{l s='Yes' mod='mokejimai'}
					<input type="radio" name="sandbox" value="0" {$sandboxOff} />{l s='No' mod='mokejimai'}
				</td>
			</tr>
			<tr>
				<td width="200" style="height: 35px;">{l s='Display payments list:' mod='mokejimai'}</td>
				<td>
					<input type="radio" name="paymentList" value="1" {$payListOn} />{l s='Yes' mod='mokejimai'}
					<input type="radio" name="paymentList" value="0" {$payListOff} />{l s='No' mod='mokejimai'}
				</td>
			</tr>
			<tr>
				<td width="200" style="height: 35px;">{l s='Default payment country:' mod='mokejimai'}</td>
				<td>
					<select name="defaultCountry">
					{foreach from=$countries item=country}
        				{if $country->getCode() == $defaultCountry}
        				<option selected="selected" value="{$country->getCode()}">{$country->getTitle()}</option>
        				{else}
        				<option value="{$country->getCode()}">{$country->getTitle()}</option>
        				{/if}
        			{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input class="button" name="btnSubmit" value="{l s='Update settings' mod='mokejimai'}" type="submit" />
				</td>
			</tr>
		</table>
	</fieldset>
</form>