{* Dodajemy atrybut data-save-url do kontenera *}
<div class="card mt-3 cart-notes-container" data-save-url="{$cart_notes_url}">
    <div class="card-header">
        <h3>{l s='Personalizacja produktów' mod='cartnotes'}</h3>
        <p class="small text-muted">{l s='Wpisz tekst grawera i kliknij przycisk Zapisz.' mod='cartnotes'}</p>
    </div>
    <div class="card-body">
        {foreach from=$products item=product}
            <div class="form-group row" style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <div class="col-md-3">
                    {if isset($product.cover.bySize.small_default.url)}
                        <img src="{$product.cover.bySize.small_default.url}" style="width: 50px; float:left; margin-right: 10px;">
                    {/if}
                    <strong>{$product.name}</strong><br>
                    {if isset($product.attributes_small)}
                        <small>{$product.attributes_small}</small>
                    {/if}
                </div>
                <div class="col-md-9">
                    {assign var="key" value="{$product.id_product}_{$product.id_product_attribute}"}
                    
                    <textarea 
                        class="form-control cart-note-input" 
                        rows="3" 
                        data-id-product="{$product.id_product}" 
                        data-id-attribute="{$product.id_product_attribute}"
                        placeholder="{l s='Wpisz tekst grawera...' mod='cartnotes'}"
                        style="margin-bottom: 10px;"
                    >{if isset($existing_notes[$key])}{$existing_notes[$key]}{/if}</textarea>

                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-primary btn-sm save-note-btn">
                            <i class="material-icons">save</i> {l s='Zapisz opis' mod='cartnotes'}
                        </button>
                        <span class="save-status text-success ml-2" style="display:none; margin-left: 10px; font-weight:bold;">
                            <i class="material-icons">check_circle</i> {l s='Zapisano!' mod='cartnotes'}
                        </span>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
</div>