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
</style>

{capture name=path}{l s='Paysera.com payment' mod='mokejimai'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='mokejimai'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>{l s='Mokejimai.lt payment' mod='mokejimai'}</h3>

<table>
    <tr>
        <td style="width:210px">
            <a href="http://www.mokejimai.lt/" target="_blank">
                <img src="{$this_path_ssl}/img/logo-{if $isoCode eq 'lt'}lt{else}en{/if}.png"
                     alt="{l s='Mokejimai' mod='mokejimai'}" class="logo"/>
            </a>
        </td>
        <td>
            {l s='You have chosen to pay by mokejimai.lt.' mod='mokejimai'}<br/>
            {l s='Here is a short summary of your order:' mod='mokejimai'}
        </td>
    </tr>
    <tr>
        <td colspan="2" height="10"></td>
    </tr>
    <tr>
        <td>{l s='The total amount of your order is' mod='mokejimai'}</td>
        <td>
            <span id="amount_{$currencies.0.id_currency}" class="price">{$amount} {$currency}</span>
        </td>
    </tr>
</table>

{if $displayPayments == 1}
    <table>
        <tr>
            <td style="width:210px">{l s='Select payment country' mod='mokejimai'}</td>
            <td>
                <select class="payment-country-select">
                    {foreach from=$payMethods item=country}
                        <option {if $country->getCode() == $defaultCountry} selected="selected" {/if}
                                value="{$country->getCode()}">{$country->getTitle()}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
    </table>
{/if}

<form action="{$link->getModuleLink('mokejimai', 'redirect')}" method="post" id="paysera-form">
    {if $displayPayments == 1}
        <div id="payment-content">
            <div class="payment-methods-wrapper">
                {foreach from=$payMethods item=country}
                    <div id="{$country->getCode()}" class="payment-countries"
                         style="display:{if $country->getCode() == $defaultCountry}table{else}none{/if};">
                        {foreach from=$country->getGroups() item=group}
                            <div class="payment-group-wrapper">
                                <div class="payment-group-title">{$group->getTitle()}</div>
                                {foreach from=$group->getPaymentMethods() item=paymentMethod}
                                    <div class="payment-item">
                                        <input type="radio" class="radio" name="payment"
                                               value="{$paymentMethod->getKey()}" class="payment-radio"/>
                                        <img src="{$paymentMethod->getLogoUrl()}" title="{$paymentMethod->getTitle()}"
                                             alt="{$paymentMethod->getTitle()}" class="payment-logo"/>

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
        <a href="{$base_dir_ssl}order.php?step=3"
           class="button_large">{l s='Other payment methods' mod='cashondelivery'}</a>
        <input type="submit" name="submit" value="{l s='I confirm my order' mod='mokejimai'}" class="exclusive_large"/>
    </p>
</form>
