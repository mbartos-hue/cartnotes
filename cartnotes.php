<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CartNotes extends Module
{
    public function __construct()
    {
        $this->name = 'cartnotes';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Ty';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Notatki do pozycji w koszyku');
        $this->description = $this->l('Pozwala dodać opis do każdego produktu w koszyku.');
    }

    public function install()
    {
        // Tworzymy tabelę na dane
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cart_notes` (
            `id_cart_note` int(11) NOT NULL AUTO_INCREMENT,
            `id_cart` int(11) NOT NULL,
            `id_product` int(11) NOT NULL,
            `id_product_attribute` int(11) NOT NULL,
            `note` text,
            PRIMARY KEY (`id_cart_note`),
            UNIQUE KEY `unique_item` (`id_cart`, `id_product`, `id_product_attribute`)
        ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;";

        return parent::install() &&
            Db::getInstance()->execute($sql) &&
            $this->registerHook('displayShoppingCartFooter') && // Wyświetlanie w koszyku
			$this->registerHook('displayProductAdditionalInfo') && // <--- Ten hook jest kluczowy
            $this->registerHook('displayAdminOrderMain') &&     // Wyświetlanie w zamówieniu (Admin)
            $this->registerHook('header');                      // Dodanie JS
    }

    public function hookHeader()
    {
        // Ładujemy JS tylko w koszyku
        if ($this->context->controller->php_self == 'cart' || $this->context->controller->php_self == 'product') {
            $this->context->controller->addJS($this->_path . 'views/js/script.js');
			$this->context->controller->addCSS($this->_path . 'views/css/style.css');
            // Przekazujemy URL do kontrolera zapisu
            Media::addJsDef([
                'cartNotesUrl' => $this->context->link->getModuleLink('cartnotes', 'save')
            ]);
        }
    }

    public function hookDisplayShoppingCartFooter($params)
	{
		$cart = $this->context->cart;

		// Generujemy link do kontrolera zapisu
		$save_url = $this->context->link->getModuleLink('cartnotes', 'save');

		$products = $cart->getProducts();
		$notes = [];

		// Sprawdź czy tabela istnieje (zabezpieczenie)
		try {
			$sql = "SELECT * FROM " . _DB_PREFIX_ . "cart_notes WHERE id_cart = " . (int)$cart->id;
			$rows = Db::getInstance()->executeS($sql);
			if ($rows) {
				foreach ($rows as $row) {
					$key = $row['id_product'] . '_' . $row['id_product_attribute'];
					$notes[$key] = $row['note'];
				}
			}
		} catch (Exception $e) {
			// Tabela może nie istnieć, jeśli moduł nie został zresetowany
		}

		$this->context->smarty->assign([
			'products' => $products,
			'existing_notes' => $notes,
			'cart_notes_url' => $save_url, // <--- PRZEKAZUJEMY URL TUTAJ
		]);

		return $this->display(__FILE__, 'views/templates/hook/cart_display.tpl');
	}

    // Wyświetlanie w panelu Admina w zamówieniu
    public function hookDisplayAdminOrderMain($params)
    {
        // W PS 1.7/8 $params['id_order']
        $id_order = $params['id_order'];
        $order = new Order($id_order);
        
        // Pobieramy notatki na podstawie ID koszyka z zamówienia
        $sql = "SELECT cn.*, pl.name as product_name 
                FROM " . _DB_PREFIX_ . "cart_notes cn
                LEFT JOIN " . _DB_PREFIX_ . "product_lang pl ON (cn.id_product = pl.id_product AND pl.id_lang = " . (int)$this->context->language->id . ")
                WHERE cn.id_cart = " . (int)$order->id_cart;
        
        $notes = Db::getInstance()->executeS($sql);

        if (empty($notes)) {
            return '';
        }

        $this->context->smarty->assign([
            'order_notes' => $notes
        ]);

        return $this->display(__FILE__, 'views/templates/hook/admin_order.tpl');
    }
	
	// Nowa funkcja wyświetlająca panel na stronie produktu
	public function hookDisplayProductAdditionalInfo($params)
	{
		$id_product = (int)$params['product']['id_product'];
		$cart = $this->context->cart;

		if (!$cart || !$cart->products) {
			return ''; // Pusty koszyk = brak przycisku
		}

		// 1. Pobieramy wszystkie produkty z koszyka
		$cart_products = $cart->getProducts();
		$matched_products = [];

		// 2. Filtrujemy: Szukamy w koszyku TYLKO tego produktu, na którym jesteśmy
		foreach ($cart_products as $p) {
			if ($p['id_product'] == $id_product) {
				$matched_products[] = $p;
			}
		}

		// Jeśli tego produktu nie ma w koszyku, nic nie wyświetlamy
		if (empty($matched_products)) {
			return '';
		}

		// 3. Pobieramy notatki dla znalezionych produktów
		$notes = [];
		$files = [];
		try {
			$sql = "SELECT * FROM " . _DB_PREFIX_ . "cart_notes WHERE id_cart = " . (int)$cart->id;
			$rows = Db::getInstance()->executeS($sql);
			if ($rows) {
				foreach ($rows as $row) {
					// Klucz to ID Produktu + ID Atrybutu (wariantu)
					$key = $row['id_product'] . '_' . $row['id_product_attribute'];
					$notes[$key] = $row['note'];
					$files[$key] = $row['file_name'];
				}
			}
		} catch (Exception $e) {}

		// 4. Przekazujemy dane do nowego szablonu
		$this->context->smarty->assign([
			'matched_products' => $matched_products, // Tylko warianty tego produktu
			'existing_notes' => $notes,
			'existing_files' => $files,
			'upload_dir_url' => $this->context->link->getBaseLink() . 'modules/cartnotes/uploads/',
			'cart_notes_url' => $this->context->link->getModuleLink('cartnotes', 'save'),
		]);

		return $this->display(__FILE__, 'views/templates/hook/product_cart_panel.tpl');
	}
}
