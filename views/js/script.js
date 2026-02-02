document.addEventListener("DOMContentLoaded", function() {
    var $ = jQuery;

    // Obsługa nazwy pliku (kosmetyka)
    $(document).on('change', '.cart-note-file', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    // Obsługa przycisku ZAPISZ
    $(document).on('click', '.save-note-btn', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        // Szukamy najbliższego kontenera (działa w koszyku i w modalu)
        var $container = $btn.closest('.cart-notes-container'); 
        var saveUrl = $container.data('save-url'); // Pobieramy URL z kontenera
        
        if (!saveUrl) {
            alert('Błąd konfiguracji: Brak URL zapisu.');
            return;
        }

        var $row = $btn.closest('.note-row'); // Szukamy wiersza (klasa dodana w szablonie)
        // Jeśli nie znajdzie .note-row (wersja koszykowa miała col-md-9), szukamy po staremu
        if ($row.length === 0) $row = $btn.closest('.col-md-9');
        if ($row.length === 0) $row = $btn.closest('.col-12'); // Wersja modal

        var $textarea = $row.find('.cart-note-input');
        var $fileInput = $row.find('.cart-note-file');
        var $status = $row.find('.save-status');
        var $fileArea = $row.find('.file-status-area');

        var formData = new FormData();
        formData.append('ajax', true);
        formData.append('action', 'saveCartNote');
        formData.append('id_product', $textarea.data('id-product'));
        formData.append('id_attribute', $textarea.data('id-attribute'));
        formData.append('note', $textarea.val());

        if ($fileInput.length > 0 && $fileInput[0].files.length > 0) {
            formData.append('file_upload', $fileInput[0].files[0]);
        }

        var originalText = $btn.html();
        $btn.prop('disabled', true).text('...');
        $status.hide();

        $.ajax({
            type: 'POST',
            url: saveUrl,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var res = (typeof response === 'string') ? JSON.parse(response) : response;
                $btn.prop('disabled', false).html(originalText);

                if (res.success) {
                    $status.fadeIn();
                    setTimeout(function() { $status.fadeOut(); }, 3000);

                    if (res.file_url) {
                        var linkHtml = '<span class="badge badge-info p-2"><i class="material-icons" style="font-size:14px;">attach_file</i> <a href="' + res.file_url + '" target="_blank" style="color:white;">Pobierz plik</a></span>';
                        $fileArea.html(linkHtml);
                        $fileInput.val('');
                        $fileInput.next('.custom-file-label').html('Wybierz plik...');
                    }
                } else {
                    alert('Błąd: ' + res.message);
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(originalText);
                alert('Błąd serwera.');
            }
        });
    });
});