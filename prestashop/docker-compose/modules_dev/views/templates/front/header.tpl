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
        /* Some nice default stylings to ensure they look good if theme doesn't provide */
        padding: 5px 7px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        margin-bottom: 5px;
        box-shadow: 2px 2px 4px 0 rgba(0,0,0,.2);
    }
    
    /* Position Top-Right (if Theme allows absolute positioning for flags container) */
    .product-flag.productbadge-{$badge.id_productbadge|intval}.pb-right {
        float: right;
        clear: right;
    }
    {/foreach}

    /* Helper for top-right alignment if the theme uses absolute positioning on the ul container */
    .product-flags {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .product-flags .pb-right {
        align-self: flex-end;
    }
</style>
{/if}
