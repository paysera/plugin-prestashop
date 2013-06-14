{if $status == 'ok'}
	<p>{l s='Your order on' mod='mokejimai'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='mokejimai'}
		<br /><br />
		Apmokėjimą už prekes gavome ir Jūsų užsakymas Nr. <b>{$id_order}</b> perduotas vykdymui.<br/>
		<br/>
		Užsakymą peržiūrėti galite paspaudę <a href="history.php">šią nuorodą</a>.<br/>
		<br/>
		Apie tolimesnę užsakymo vykdymo eigą būsite informuoti elektroniniu paštu.<br/>
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='mokejimai'} 
		<a href="{$base_dir}contact-form.php">{l s='customer support' mod='mokejimai'}</a>.
	</p>
{/if}