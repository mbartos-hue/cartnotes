<?php

class CartNotesSaveModuleFrontController extends ModuleFrontController
{
    public $auth = false; // Dostępne dla niezalogowanych (gości)
    public $ajax = true;  // Informacja dla Presty

    public function init()
    {
        parent::init();
    }

    public function postProcess()
    {
        // 1. Czyścimy bufor wyjścia. 
        // To usuwa wszelkie "Notice" czy spacje wygenerowane przez inne moduły/core przed tym momentem.
        if (ob_get_length()) {
            ob_end_clean();
        }

        // 2. Nagłówek JSON
        header('Content-Type: application/json');

        try {
            $id_cart = $this->context->cart->id;
            $id_product = (int)Tools::getValue('id_product');
            $id_attribute = (int)Tools::getValue('id_attribute');
            $note = pSQL(Tools::getValue('note')); // Zabezpieczenie SQL Injection

            // Walidacja wstępna
            if (!$id_cart) {
                throw new Exception('Brak ID koszyka (sesja wygasła?)');
            }
            if (!$id_product) {
                throw new Exception('Brak ID produktu');
            }

            // 3. Sprawdzenie czy tabela istnieje (częsty błąd przy braku resetu modułu)
            $db = Db::getInstance();
            /* Opcjonalne sprawdzenie, można wyłączyć dla wydajności po testach
            $table_exists = $db->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . "cart_notes'");
            if (empty($table_exists)) {
                throw new Exception('Tabela ' . _DB_PREFIX_ . 'cart_notes nie istnieje! Zresetuj moduł.');
            }
            */

            // 4. Logika zapisu
            $sqlCheck = "SELECT id_cart_note FROM " . _DB_PREFIX_ . "cart_notes 
                         WHERE id_cart = " . (int)$id_cart . " 
                         AND id_product = " . (int)$id_product . " 
                         AND id_product_attribute = " . (int)$id_attribute;
            
            $exists = $db->getValue($sqlCheck);

            if ($exists) {
                $sql = "UPDATE " . _DB_PREFIX_ . "cart_notes 
                        SET note = '$note' 
                        WHERE id_cart_note = " . (int)$exists;
            } else {
                $sql = "INSERT INTO " . _DB_PREFIX_ . "cart_notes (id_cart, id_product, id_product_attribute, note)
                        VALUES (" . (int)$id_cart . ", " . (int)$id_product . ", " . (int)$id_attribute . ", '$note')";
            }

            $result = $db->execute($sql);

            if (!$result) {
                throw new Exception('Błąd SQL: ' . $db->getMsgError());
            }

            // SUKCES
            die(json_encode([
                'success' => true, 
                'message' => 'Zapisano pomyślnie'
            ]));

        } catch (Exception $e) {
            // BŁĄD - Zwracamy go jako JSON, żeby JS nie wywalił błędu składni
            die(json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]));
        }
    }
}