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
    jQuery(function ($) {
        $('select.payment-country-select').change(function () {
            var value = $('option:selected', this).val();

            $('input.payment-radio').each(function () {
                $(this).attr('checked', false);
            });

            $('div.payment-countries').hide();

            $('div.payment-countries').each(function () {
                if ($(this).attr('id') == value) {
                    $(this).show();
                    return false;
                }
            });
        });

        $('img.payment-logo').click(function () {
            $(this).prev().attr('checked', true);
        });
    });
    {/literal}
</script>

<style type="text/css">
    img.logo {
        float: left;
        margin: 0px 10px 5px 0px;
    }

    div.payment-group-title {
        font-weight: 700;
        padding: 15px 0;
    }

    div.payment-group-wrapper {
        display: table;
        width: 100%;
        margin: 0 0 15px 0;
    }

    div.payment-item {
        display: table-cell;
        float: left;
        height: 70px;
        width: 250px;
    }

    div.payment-item input {
        display: inline;
        margin: 0 10px 0 0;
        vertical-align: middle;
    }

    div.payment-item img {
        display: inline;
        cursor: pointer;
        vertical-align: middle;
    }

    div.clear {
        clear: both;
    }
    .col-sm-9 { width:100% !important; }
    .col-sm-3 { display:none !important; }
</style>

{capture name=path}{l s='Paysera.com payment' mod='paysera'}{/capture}


<h2>{l s='Order summary' mod='paysera'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>{l s='Paysera payment' mod='paysera'}</h3>

<table>
    <tr>
        <td style="width:210px">
            <a href="http://www.paysera.com/" target="_blank">
                <img src="{$this_path_ssl}/img/logo-{if $isoCode eq 'lt'}lt{else}en{/if}.png"
                     alt="{l s='Paysera' mod='paysera'}" class="logo"/>
            </a>
        </td>
        <td>
            {l s='You have chosen to pay by Paysera' mod='paysera'}<br/>
            {l s='Here is a short summary of your order:' mod='paysera'}
        </td>
    </tr>
    <tr>
        <td colspan="2" height="10"></td>
    </tr>
    <tr>
        <td>{l s='The total amount of your order is' mod='paysera'}</td>
        <td>
            <span id="amount_{$currencies.0.id_currency|escape:'quotes'}" class="price">{$amount|escape:'quotes'} {$currency|escape:'quotes'}</span>
        </td>
    </tr>
</table>

{if $displayPayments == 1}
    <table>
        <tr>
            <td style="width:210px">{l s='Select payment country' mod='paysera'}</td>
            <td>
                <select class="payment-country-select">
                    {foreach from=$payMethods item=country}
                        <option {if $country->getCode() == $defaultCountry} selected="selected" {/if}
                                value="{$country->getCode()|escape:'quotes'}">{$country->getTitle()|escape:'quotes'}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
    </table>
{/if}

<form action="{$link->getModuleLink('paysera', 'redirect', [], true)|escape:'html'}" method="post" id="paysera-form">
    {if $displayPayments == 1}
        <div id="payment-content">
            <div class="payment-methods-wrapper">
                {foreach from=$payMethods item=country}
                    <div id="{$country->getCode()|escape:'quotes'}" class="payment-countries"
                         style="display:{if $country->getCode() == $defaultCountry}table{else}none{/if};">
                        {foreach from=$country->getGroups() item=group}
                            <div class="payment-group-wrapper">
                                <div class="payment-group-title">{$group->getTitle()|escape:'quotes'}</div>
                                {foreach from=$group->getPaymentMethods() item=paymentMethod}
                                    <div class="payment-item">
                                        <input type="radio" class="radio" name="payment_method"
                                               value="{$paymentMethod->getKey()|escape:'quotes'}" class="payment-radio"/>
                                        <img src="{$paymentMethod->getLogoUrl()|escape:'quotes'}" title="{$paymentMethod->getTitle()}"
                                             alt="{$paymentMethod->getTitle()|escape:'quotes'}" class="payment-logo"/>

                                        <div class="clear"></div>
                                    </div>
                                {/foreach}
                                <div class="clear"></div>
                            </div>
                        {/foreach}
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}
    <p class="cart_navigation">
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">
        {l s='Other payment methods' mod='paysera'}</a>
        <input type="submit" name="submit" value="{l s='I confirm my order' mod='paysera'}" class="exclusive_large"/>
    </p>
</form>
