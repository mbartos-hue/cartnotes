<div class="product-cart-notes-trigger" style="margin-top: 20px;">
    <button type="button" class="btn btn-secondary btn-lg btn-block" onclick="toggleCartNotesModal()">
        <i class="material-icons">edit_note</i> {l s='Personalizuj sztuki w koszyku' mod='cartnotes'} 
        <span class="badge badge-light ml-2">{$matched_products|count}</span>
    </button>
</div>

<div id="cart-notes-modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="background:#fff; width:90%; max-width:600px; margin:50px auto; padding:20px; border-radius:8px; box-shadow:0 0 15px rgba(0,0,0,0.2); max-height:90vh; overflow-y:auto;">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="m-0">{l s='Twoje produkty w koszyku' mod='cartnotes'}</h4>
            <button type="button" class="close" onclick="toggleCartNotesModal()" style="font-size:2rem;">&times;</button>
        </div>

        <div class="cart-notes-container" data-save-url="{$cart_notes_url}">
            {foreach from=$matched_products item=product}
                <div class="row note-row" style="border-bottom: 1px solid #eee; padding: 15px 0; background: #fafafa; margin-bottom: 10px; border-radius: 5px;">
                    
                    {* Info o wariancie *}
                    <div class="col-12 mb-2">
                        <strong>{$product.name}</strong>
                        {if isset($product.attributes_small)}
                            <br><span class="badge badge-info">{$product.attributes_small}</span>
                        {/if}
                        <span class="float-right text-muted">Ilość: {$product.cart_quantity}</span>
                    </div>

                    {* Formularz *}
                    <div class="col-12">
                        {assign var="key" value="{$product.id_product}_{$product.id_product_attribute}"}
                        
                        <textarea 
                            class="form-control cart-note-input mb-2" 
                            rows="2" 
                            data-id-product="{$product.id_product}" 
                            data-id-attribute="{$product.id_product_attribute}"
                            placeholder="{l s='Wpisz tekst grawera...' mod='cartnotes'}"
                        >{if isset($existing_notes[$key])}{$existing_notes[$key]}{/if}</textarea>

                        <div class="input-group mb-2">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input cart-note-file" id="modal_file_{$key}" name="file_upload">
                                <label class="custom-file-label" for="modal_file_{$key}">{l s='Plik...' mod='cartnotes'}</label>
                            </div>
                        </div>

                        <div class="file-status-area mb-2">
                            {if isset($existing_files[$key]) && $existing_files[$key]}
                                <span class="badge badge-info p-2">
                                    <i class="material-icons" style="font-size:14px;">attach_file</i> 
                                    <a href="{$upload_dir_url}{$existing_files[$key]}" target="_blank" style="color:white;">{l s='Pobierz plik' mod='cartnotes'}</a>
                                </span>
                            {/if}
                        </div>

                        <div class="d-flex align-items-center justify-content-end">
                            <span class="save-status text-success mr-3" style="display:none; font-weight:bold;">
                                <i class="material-icons">check_circle</i> Zapisano!
                            </span>
                            <button type="button" class="btn btn-primary btn-sm save-note-btn">
                                <i class="material-icons">save</i> {l s='Zapisz' mod='cartnotes'}
                            </button>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>

        <div class="text-center mt-3">
            <button class="btn btn-outline-secondary" onclick="toggleCartNotesModal()">Zamknij okno</button>
        </div>
    </div>
</div>

<script>
function toggleCartNotesModal() {
    var el = document.getElementById('cart-notes-modal-overlay');
    if (el.style.display === 'none') {
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }
}
</script>