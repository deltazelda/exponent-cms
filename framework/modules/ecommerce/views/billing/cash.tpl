{css unique="general-ecom" link="`$asset_path`css/creditcard-form.css"}

{/css}

<div class="billing-method payflowpro creditcard-form">
    <h4>{"Pay By Cash"|gettext}</h4>
    {form name="ccinfoform`$key`" controller=cart action=preprocess}
        {control type="hidden" name="billingcalculator_id" value=$calcid}
        {$billing->form}
        <button id="continue-checkout{$key}" type="submit" class="awesome {$smarty.const.BTN_SIZE} {$smarty.const.BTN_COLOR}">{"Continue Checkout"|gettext}</button>
    {/form}
</div>
