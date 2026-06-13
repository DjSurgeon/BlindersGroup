{*
* 2007-2024 PrestaShop
*
* NOTICE OF LICENSE
* ...
*}

<div class="m-b-1 m-t-1">
    <h2>{l s='Product Badges' mod='productbadges'}</h2>
    
    {if isset($productbadges) && $productbadges|count > 0}
        <div class="form-group">
            <label class="form-control-label">{l s='Select badges for this product:' mod='productbadges'}</label>
            <div class="checkbox-list">
                {foreach from=$productbadges item=badge}
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="productbadges[]" value="{$badge.id_productbadge|intval}"
                                {if in_array($badge.id_productbadge, $assigned_badges)}checked="checked"{/if}>
                            <span class="badge" style="background-color: {$badge.bg_color|escape:'html'}; color: {$badge.text_color|escape:'html'}; padding: 5px 10px; border-radius: 4px; margin-left: 10px;">
                                {$badge.text|escape:'html'}
                            </span>
                        </label>
                    </div>
                {/foreach}
            </div>
            <small class="form-text text-muted">
                {l s='Check the badges you want to display on this product.' mod='productbadges'}
            </small>
        </div>
    {else}
        <div class="alert alert-info">
            {l s='No active badges found. Please create some in the Product Badges menu first.' mod='productbadges'}
        </div>
    {/if}
</div>
