{*
* 2007-2024 PrestaShop
*
* NOTICE OF LICENSE
* ...
*}

{if isset($productbadges_css) && $productbadges_css|count > 0}
<style>
    {foreach from=$productbadges_css item=badge}
    .product-flag.productbadge-{$badge.id_productbadge|intval} {
        background-color: {$badge.bg_color|escape:'htmlall':'UTF-8'} !important;
        color: {$badge.text_color|escape:'htmlall':'UTF-8'} !important;
    }
    {/foreach}
</style>
{/if}
