<div class="panel">
    <div class="panel-heading">
        <i class="icon-comment"></i> {l s='Personalizacja z koszyka' mod='cartnotes'}
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{l s='Produkt' mod='cartnotes'}</th>
                    <th>{l s='Treść personalizacji' mod='cartnotes'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$order_notes item=note}
                <tr>
                    <td>
                        <strong>{$note.product_name}</strong><br>
                        <small>ID: {$note.id_product} | Attr ID: {$note.id_product_attribute}</small>
                    </td>
                    <td>
                        <pre style="background:none; border:none; font-family:inherit;">{$note.note}</pre>
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
