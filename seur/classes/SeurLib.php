<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Maria Jose Santos <mariajose.santos@ebolution.com>
 * @copyright 2022 Seur Transporte
 * @license https://seur.com/ Proprietary
 */

if (!defined('_PS_VERSION_'))
	exit;

if (!defined('SEUR_MODULE_NAME'))
	define('SEUR_MODULE_NAME', 'seur');

class SeurLib
{
    const CODPaymentName = 'SEUR Contra Reembolso';
    const CODPaymentModule = 'seurcashondelivery';
    const SKEW = 60; // margen de 60s para evitar caducar en mitad de una petición

    public static $baleares_states = array(
		'ES-IB' => 'Baleares'
	);

	public static $canarias_states = array(
		'ES-TF' => 'Santa Cruz de Tenerife', 'ES-GC' => 'Las Palmas'
	);

	public static $ceuta_melilla_states = array(
		'ES-CE' => 'Ceuta', 'ES-ML' => 'Melilla'
	);

	public static $spain_states = array(
		'ES-VI' => 'Alava',     'ES-AB' => 'Albacete',   'ES-A' => 'Alicante',       'ES-AL' => 'Almeria',          'ES-O' => 'Asturias',
		'ES-AV' => 'Avila',     'ES-BA' => 'Badajoz',    'ES-B' => 'Barcelona',      'ES-BU' => 'Burgos',           'ES-CC' => 'Caceres',
		'ES-CA' => 'Cadiz',     'ES-S' => 'Cantabria',   'ES-CS'  => 'Castellon',    'ES-CR' => 'Ciudad Real',      'ES-CO' => 'Cordoba',
		'ES-CU' => 'Cuenca',    'ES-GI' => 'Gerona',     'ES-GR' => 'Granada',       'ES-GU' => 'Guadalajara',      'ES-SS' => 'Guipuzcua',
		'ES-H' => 'Huelva',     'ES-HU' => 'Huesca',     'ES-J' => 'Jaen',           'ES-C'  => 'La Coruña',		'ES-L' => 'Lerida',
		'ES-LO' => 'La Rioja',  'ES-LE' => 'Leon',       'ES-LU' => 'Lugo',          'ES-MA' => 'Malaga',           'ES-M' => 'Madrid',
		'ES-MU' => 'Murcia',    'ES-NA' => 'Navarra',    'ES-OU' => 'Orense',        'ES-P' => 'Palencia',          'ES-PO' => 'Pontevedra',
		'ES-SA' => 'Salamanca', 'ES-SG' => 'Segovia',    'ES-SE' => 'Sevilla',       'ES-SO' => 'Soria',            'ES-T' => 'Tarragona',
		'ES-TE' => 'Teruel',    'ES-TO' => 'Toledo',     'ES-V' => 'Valencia',       'ES-VA' => 'Valladolid',       'ES-BI' => 'Vizcaya',
		'ES-ZA' => 'Zamora',    'ES-Z' => 'Zaragoza'
	);

	public static $street_types = array(
		'AUT' => 'AUTOVIA',          'AVD' => 'AVENIDA',  'CL' => 'CALLE',     'CRT' => 'CARRETERA',
		'CTO' => 'CENTRO COMERCIAL', 'EDF' => 'EDIFICIO', 'ENS' => 'ENSANCHE', 'GTA' => 'GLORIETA',
		'GRV' => 'GRAN VIA',         'PSO' => 'PASEO',    'PZA' => 'PLAZA',    'POL' => 'POLIGONO INDUSTRIAL',
		'RAM' => 'RAMBLA',           'RDA' => 'RONDA',    'ROT' => 'ROTONDA',  'TRV' => 'TRAVESIA',
		'URB' => 'URBANIZACION'
	);

	public static $seur_countries = array(
		'ES' => 'ESPAÑA', 'PT' => 'PORTUGAL'
	);

	public static $seur_zones = array(
		0 => 'Provincia',    1 => 'Peninsula',    2 => 'Portugal',
		3 => 'Baleares',     4 => 'Canarias',     5 => 'Ceuta/Melilla'
	);

	public static function displayErrors($error = null)
	{
		if (!empty($error))
		{
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				echo '<div class="error"><p>'.$error.'</p></div>';
			else
				echo '<div class="error"><p><img src="../img/admin/warning.gif" border="0" alt="Error Icon" /> '.$error.'</p></div>'; // @TODO echo ??
		}
	}

	public static function getOrderPos($id_cart)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur2_order_pos`
			WHERE `id_cart` = '.(int)$id_cart
		);
	}

    public static function isPickup($servicio, $producto){
        return ($servicio==1 || $servicio==77) && $producto==48;
    }

	public static function getMerchantData($id_merchant = 1)
	{
	    if($id_merchant == 0)
            $id_merchant = 1;

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur2_ccc`
			WHERE `id_seur_ccc` = '.(int)$id_merchant
		);
	}

    //Añadido para cliente específico con internacional
    public static function getCCC($country_iso_code)
    {
        $id_seur_ccc = '';
        $list_ccc = SeurCCC::getListCCC();
        if (count($list_ccc)==1) {
            return $list_ccc[0]['id_seur_ccc'];
        }
        foreach ($list_ccc as $item_ccc) {
            if (SeurLib::isInternationalCustomer($item_ccc['ccc']) &&
                SeurLib::isInternationalShipping($country_iso_code)) {
               return $item_ccc['id_seur_ccc'];
            } else {
                if ($id_seur_ccc == '') {
                    if (! SeurLib::isInternationalCustomer($item_ccc['ccc'])) {
                        $id_seur_ccc = $item_ccc['id_seur_ccc'];
                    }
                }
            }
        }
        return $id_seur_ccc;
    }

    public static function isEuropeanShipping($id_seur_order) {
        $seur_order = new SeurOrder($id_seur_order);
        $country_iso_code =  Country::getIsoById((int)$seur_order->id_country);
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue("
            SELECT iso_code
            FROM `"._DB_PREFIX_."seur2_european_countries`
            WHERE iso_code = '".$country_iso_code."'"
        );
    }

    public static function isInternationalShipping($country_iso_code) {
        return $country_iso_code != 'ES' && $country_iso_code != 'PT' && $country_iso_code != 'AD';
    }

    public static function isInternationalCustomer($ccc) {
        return $ccc == 12642;
    }

    public static function isPickupFrio($id_seur_ccc) {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue("
            SELECT product
            FROM `"._DB_PREFIX_."seur2_order`
            WHERE DATE(date_labeled) = CURRENT_DATE()
            AND labeled = 1
            AND product IN (SELECT id_seur_product
                FROM `"._DB_PREFIX_."seur2_products`
                WHERE name like '%FRIO%')
            AND id_seur_ccc = ".$id_seur_ccc
        );
    }

    public static function generateRef() {
        return md5(uniqid(rand(), true));
    }

    public static function getMerchantField($campo,$id_merchant = 1)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT '.pSQL($campo).'
			FROM `'._DB_PREFIX_.'seur2_ccc`
			WHERE `id_seur_ccc` = '.(int)$id_merchant
		);
	}

	public static function setMerchantField($campo, $valor)
	{
		return Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'seur_merchant`
			SET `'.pSQL($campo).'`="'.pSQL($valor).'"
			WHERE `id_seur_datos` = 1'
		);
	}

	public static function isPricesConfigured()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT COUNT( * )
			FROM  `'._DB_PREFIX_.'delivery`
			WHERE  `price` != 0
			AND  `id_carrier` IN (
				SELECT  `id_seur_carrier` AS `id`
				FROM  `'._DB_PREFIX_.'seur_history`
				WHERE  `active` =1
			)'
		);
	}

	public static function getSeurCarrier($id_carrier)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT sc.*,c.name
			FROM `'._DB_PREFIX_.'seur2_carrier` sc
			LEFT JOIN `'._DB_PREFIX_.'carrier` c ON c.id_reference=sc.carrier_reference AND c.active=1 AND c.deleted=0
			WHERE c.`id_carrier` = "'.pSQL($id_carrier).'"'
		);
	}

    public static function getSeurPOSCarrier()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT sc.*,c.name
			FROM `'._DB_PREFIX_.'seur2_carrier` sc
			LEFT JOIN `'._DB_PREFIX_.'carrier` c ON c.id_reference=sc.carrier_reference AND c.active=1 AND c.deleted=0
			WHERE sc.shipping_type = 2');
    }

	public static function getSeurCarriers($active = true)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT sc.*, c.id_carrier
			FROM `'._DB_PREFIX_.'seur2_carrier` sc
			LEFT JOIN `'._DB_PREFIX_.'carrier` c ON c.id_reference=sc.carrier_reference AND c.deleted=0
			'.($active ? 'WHERE c.active = 1' : '')
		);
	}

	public static function getLastSeurCarriers()
	{
		Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('UPDATE `'._DB_PREFIX_.'seur_history` SET `active`= 0');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT `id_seur_carrier` AS `id`, `type`
			FROM `'._DB_PREFIX_.'seur_history`
			GROUP BY `type`'
		);
	}

	public static function updateSeurCarriers($new_carriers_seur)
	{
		$olds_carriers_seur = self::getSeurCarriers(false);

		$sql_new_carriers = 'INSERT IGNORE INTO `'._DB_PREFIX_.'seur_history` VALUES ';
		$sql_disable = 'UPDATE `'._DB_PREFIX_.'seur_history` SET `active` = 0 WHERE ';
		$sql_enable = 'UPDATE `'._DB_PREFIX_.'seur_history` SET `active` = 1 WHERE ';

		$enable_olds_carriers = false;

		foreach ($olds_carriers_seur as $old_carrier)
		{
			foreach ($new_carriers_seur as $key_new => $new_carrier_seur)
			{
				if (($new_carrier_seur['id'] == $old_carrier['id']) && ($new_carrier_seur['type'] == $old_carrier['type']))
				{
					if ($old_carrier['active'] == 0)
					{
						$sql_enable .= ' (`id_seur_carrier` = '.(int)$old_carrier['id'].' AND `type` = "'.pSQL($old_carrier['type']).'") OR ';
						$sql_disable .= '`type` ="'.pSQL($old_carrier['type']).'" OR ';
						$enable_olds_carriers = true;
					}
					unset($new_carriers_seur[$key_new]);
				}
			}
		}

		foreach ($new_carriers_seur as $new_carrier_seur)
		{
			$sql_new_carriers .= '('.(int)$new_carrier_seur['id'].',"'.pSQL($new_carrier_seur['type']).'",1),';
			$sql_disable .= '`type` ="'.pSQL($new_carrier_seur['type']).'" OR ';
		}

		$sql_disable = trim($sql_disable, 'OR ');
		$sql_disable .= ';';

		$sql_enable = trim($sql_enable, 'OR ');
		$sql_enable .= ';';

		if (!empty($new_carriers_seur))
		{
			Db::getInstance()->Execute($sql_disable);

			$sql_new_carriers = trim($sql_new_carriers, ',');
			$sql_new_carriers .= ';';
			Db::getInstance()->Execute($sql_new_carriers);
		}

		if ($enable_olds_carriers)
		{
			Db::getInstance()->Execute($sql_disable);
			Db::getInstance()->Execute($sql_enable);
		}
	}

    public static function isSeurOrder($id_order)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur2_order`
			WHERE `id_order` ='.(int)$id_order
        );
    }

    public static function isSeurCarrier($id_carrier)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT sc.*,c.name
			FROM `'._DB_PREFIX_.'seur2_carrier` sc
			LEFT JOIN `'._DB_PREFIX_.'carrier` c ON c.id_reference=sc.carrier_reference
			WHERE c.`id_carrier` = "'.pSQL($id_carrier).'"'
        );
    }

    public static function isSeurPOSCarrier($id_carrier)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT sc.*,c.name
			FROM `'._DB_PREFIX_.'seur2_carrier` sc
			LEFT JOIN `'._DB_PREFIX_.'carrier` c ON c.id_reference=sc.carrier_reference
			WHERE c.`id_carrier` = "'.pSQL($id_carrier).'"
			AND sc.shipping_type = 2'
        );
    }

	public static function getSeurOrder($id_order)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur2_order`
			WHERE `id_order` ='.(int)$id_order
			);
	}

	public static function setSeurOrder($id_order, $ecb, $bultos, $peso, $labeled = false, $label_file = null, $codfee = null, $id_address_delivery = 0, $total_paid = 0, $auto_create_label = false)
	{
	    if ($ecb) {
            $exists = self::getSeurOrder($id_order);
            if ($exists) {
                $result = Db::getInstance()->Execute('
				UPDATE ' . _DB_PREFIX_ . 'seur2_order
				SET `ecb`="' . $ecb . '", `peso_bultos`=' . (float)$peso . ', `labeled`=' . (int)$labeled .', `numero_bultos`=' . (int)$bultos .
                    (($codfee != null) ? ',  `codfee`=' . (float)$codfee : '') .
                    (($total_paid != 0) ? ' ,  `total_paid`=' . (float)$total_paid : '') .
                    ((isset($label_file) && $label_file != "") ? ',  label_file="' . $label_file . '"' : '') .
                    ', date_labeled="' . date('Y-m-d') . '"
				WHERE `id_order` =' . (int)$id_order
                );
            } else {
                $result = Db::getInstance()->Execute('
				INSERT INTO `' . _DB_PREFIX_ . 'seur2_order` (id_order, ecb, numero_bultos, peso_bultos, labeled, codfee, id_address_delivery,total_paid)
				VALUES (' . (int)$id_order . ',"' . $ecb . '", ' . (int)$bultos . ',' . (float)$peso . ', 0, ' . (float)$codfee . ',' . (int)$id_address_delivery . ',' . (float)$total_paid . ');'
                );
            }
        }

        // Si está configurado el cambio automático de estado al etiquetar, cambiamos de estado.
        if (!$auto_create_label && Configuration::get('SEUR2_SENDED_ORDER') && $labeled) {
            SeurLib::markAsSended($id_order);
        }

        $payment_module = Db::getInstance()->getValue('
			SELECT `module`
			FROM `'._DB_PREFIX_.'orders`
			WHERE `id_order` = '.(int)$id_order);

        if ($payment_module=='seurcashondelivery') {
            $result = Db::getInstance()->Execute('
				UPDATE ' . _DB_PREFIX_ . 'seur2_order
				SET `cashondelivery`=' . (float)$total_paid . '
				WHERE `id_order` =' . (int)$id_order
            );
        }

        return $result ?? false;
    }

    public static function markAsSended($id_order) {
        $id_state = Configuration::get('PS_OS_SHIPPING');
        $order = new Order($id_order);

        if ($order->current_state != $id_state) {
            $history = new OrderHistory();
            $history->id_order = (int)$order->id;
            $history->changeIdOrderState((int) $id_state, $id_order, true);
            $history->addWithemail();
        }
    }

    public static function setSeurOrderExpeditionCode($id_order, $expeditionCode, $ecbs, $parcelNumbers, $label_files) {
        $result = Db::getInstance()->Execute("
				UPDATE " . _DB_PREFIX_ . "seur2_order
				SET `expeditionCode`='" . $expeditionCode . "',
                    `ecbs`='" . implode('-', $ecbs) . "',
                    `parcelNumbers`='" . implode('-', $parcelNumbers) . "',
                    `label_files`='" . implode('-', $label_files) . "'
				WHERE `id_order` =" . (int)$id_order
        );
        return $result;
    }

    public static function setOrderShippingNumber($id_order, $trackingNumber) {
        $order = new OrderCore($id_order);
        // limitar a 64 caracteres por si el transportista devuelve un número muy largo
        $trackingNumber = substr($trackingNumber, 0, 64);
        $order->setWsShippingNumber($trackingNumber);
    }

	public static function getModulosPago()
	{
		$modules = Module::getModulesOnDisk();
		$paymentModules = array();

		foreach ($modules as $module)
		{
			if ($module->tab == 'payments_gateways')
			{
				if ($module->id)
				{
					if (!get_class($module) == 'SimpleXMLElement')
						$module->country = array();

					$countries = DB::getInstance()->ExecuteS('
						SELECT `id_country`
						FROM `'._DB_PREFIX_.'module_country`
						WHERE `id_module` = '.(int)$module->id
					);

					foreach ($countries as $country)
						$module->country[] = (int)$country['id_country'];

					if (!get_class($module) == 'SimpleXMLElement')
						$module->currency = array();

					$currencies = DB::getInstance()->ExecuteS('
						SELECT `id_currency`
						FROM `'._DB_PREFIX_.'module_currency`
						WHERE `id_module` = "'.(int)$module->id.'"
					');

					foreach ($currencies as $currency)
						$module->currency[] = (int)$currency['id_currency'];

					if (!get_class($module) == 'SimpleXMLElement')

						$module->group = array();
					$groups = DB::getInstance()->ExecuteS('
						SELECT `id_group`
						FROM `'._DB_PREFIX_.'module_group`
						WHERE `id_module` = "'.(int)$module->id.'"
					');

					foreach ($groups as $group)
						$module->group[] = (int)$group['id_group'];

				}
				else
				{
					$module->country = null;
					$module->currency = null;
					$module->group = null;
				}
				$paymentModules[] = $module;
			}
		}

		return $paymentModules;
	}

    public static function displayWarningSeur()
    {
        if (version_compare(_PS_VERSION_, '1.5', '<'))
            Context::getContext()->smarty->assign('ps_14', true);

        if (!version_compare(_PS_VERSION_, '1.6', '<'))
            Context::getContext()->smarty->assign('ps_16', true);
    }

    public static function isPrinted($id_order)
    {
        return DB::getInstance()->getValue('
			SELECT `labeled`
			FROM `'._DB_PREFIX_.'seur2_order`
			WHERE `id_order` = "'.(int)$id_order.'"
		');
    }

    public static function replaceAccentedChars($text)
    {
        if (version_compare(_PS_VERSION_, '1.4.5.1', '>='))
            return Tools::replaceAccentedChars($text);

        $patterns = array(
            /* Lowercase */
            /* a  */ '/[\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}\x{0101}\x{0103}\x{0105}\x{0430}]/u',
            /* b  */ '/[\x{0431}]/u',
            /* c  */ '/[\x{00E7}\x{0107}\x{0109}\x{010D}\x{0446}]/u',
            /* d  */ '/[\x{010F}\x{0111}\x{0434}]/u',
            /* e  */ '/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{0113}\x{0115}\x{0117}\x{0119}\x{011B}\x{0435}\x{044D}]/u',
            /* f  */ '/[\x{0444}]/u',
            /* g  */ '/[\x{011F}\x{0121}\x{0123}\x{0433}\x{0491}]/u',
            /* h  */ '/[\x{0125}\x{0127}]/u',
            /* i  */ '/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}\x{0129}\x{012B}\x{012D}\x{012F}\x{0131}\x{0438}\x{0456}]/u',
            /* j  */ '/[\x{0135}\x{0439}]/u',
            /* k  */ '/[\x{0137}\x{0138}\x{043A}]/u',
            /* l  */ '/[\x{013A}\x{013C}\x{013E}\x{0140}\x{0142}\x{043B}]/u',
            /* m  */ '/[\x{043C}]/u',
            /* n  */ '/[\x{00F1}\x{0144}\x{0146}\x{0148}\x{0149}\x{014B}\x{043D}]/u',
            /* o  */ '/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}\x{014D}\x{014F}\x{0151}\x{043E}]/u',
            /* p  */ '/[\x{043F}]/u',
            /* r  */ '/[\x{0155}\x{0157}\x{0159}\x{0440}]/u',
            /* s  */ '/[\x{015B}\x{015D}\x{015F}\x{0161}\x{0441}]/u',
            /* ss */ '/[\x{00DF}]/u',
            /* t  */ '/[\x{0163}\x{0165}\x{0167}\x{0442}]/u',
            /* u  */ '/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{0169}\x{016B}\x{016D}\x{016F}\x{0171}\x{0173}\x{0443}]/u',
            /* v  */ '/[\x{0432}]/u',
            /* w  */ '/[\x{0175}]/u',
            /* y  */ '/[\x{00FF}\x{0177}\x{00FD}\x{044B}]/u',
            /* z  */ '/[\x{017A}\x{017C}\x{017E}\x{0437}]/u',
            /* ae */ '/[\x{00E6}]/u',
            /* ch */ '/[\x{0447}]/u',
            /* kh */ '/[\x{0445}]/u',
            /* oe */ '/[\x{0153}]/u',
            /* sh */ '/[\x{0448}]/u',
            /* shh*/ '/[\x{0449}]/u',
            /* ya */ '/[\x{044F}]/u',
            /* ye */ '/[\x{0454}]/u',
            /* yi */ '/[\x{0457}]/u',
            /* yo */ '/[\x{0451}]/u',
            /* yu */ '/[\x{044E}]/u',
            /* zh */ '/[\x{0436}]/u',

            /* Uppercase */
            /* A  */ '/[\x{0100}\x{0102}\x{0104}\x{00C0}\x{00C1}\x{00C2}\x{00C3}\x{00C4}\x{00C5}\x{0410}]/u',
            /* B  */ '/[\x{0411}]]/u',
            /* C  */ '/[\x{00C7}\x{0106}\x{0108}\x{010A}\x{010C}\x{0426}]/u',
            /* D  */ '/[\x{010E}\x{0110}\x{0414}]/u',
            /* E  */ '/[\x{00C8}\x{00C9}\x{00CA}\x{00CB}\x{0112}\x{0114}\x{0116}\x{0118}\x{011A}\x{0415}\x{042D}]/u',
            /* F  */ '/[\x{0424}]/u',
            /* G  */ '/[\x{011C}\x{011E}\x{0120}\x{0122}\x{0413}\x{0490}]/u',
            /* H  */ '/[\x{0124}\x{0126}]/u',
            /* I  */ '/[\x{0128}\x{012A}\x{012C}\x{012E}\x{0130}\x{0418}\x{0406}]/u',
            /* J  */ '/[\x{0134}\x{0419}]/u',
            /* K  */ '/[\x{0136}\x{041A}]/u',
            /* L  */ '/[\x{0139}\x{013B}\x{013D}\x{0139}\x{0141}\x{041B}]/u',
            /* M  */ '/[\x{041C}]/u',
            /* N  */ '/[\x{00D1}\x{0143}\x{0145}\x{0147}\x{014A}\x{041D}]/u',
            /* O  */ '/[\x{00D3}\x{014C}\x{014E}\x{0150}\x{041E}]/u',
            /* P  */ '/[\x{041F}]/u',
            /* R  */ '/[\x{0154}\x{0156}\x{0158}\x{0420}]/u',
            /* S  */ '/[\x{015A}\x{015C}\x{015E}\x{0160}\x{0421}]/u',
            /* T  */ '/[\x{0162}\x{0164}\x{0166}\x{0422}]/u',
            /* U  */ '/[\x{00D9}\x{00DA}\x{00DB}\x{00DC}\x{0168}\x{016A}\x{016C}\x{016E}\x{0170}\x{0172}\x{0423}]/u',
            /* V  */ '/[\x{0412}]/u',
            /* W  */ '/[\x{0174}]/u',
            /* Y  */ '/[\x{0176}\x{042B}]/u',
            /* Z  */ '/[\x{0179}\x{017B}\x{017D}\x{0417}]/u',
            /* AE */ '/[\x{00C6}]/u',
            /* CH */ '/[\x{0427}]/u',
            /* KH */ '/[\x{0425}]/u',
            /* OE */ '/[\x{0152}]/u',
            /* SH */ '/[\x{0428}]/u',
            /* SHH*/ '/[\x{0429}]/u',
            /* YA */ '/[\x{042F}]/u',
            /* YE */ '/[\x{0404}]/u',
            /* YI */ '/[\x{0407}]/u',
            /* YO */ '/[\x{0401}]/u',
            /* YU */ '/[\x{042E}]/u',
            /* ZH */ '/[\x{0416}]/u');

        // ö to oe
        // å to aa
        // ä to ae

        $replacements = array(
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 'ss', 't', 'u', 'v', 'w', 'y', 'z', 'ae', 'ch',
            'kh', 'oe', 'sh', 'shh', 'ya', 'ye', 'yi', 'yo', 'yu', 'zh', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'R', 'S', 'T', 'U', 'V', 'W', 'Y', 'Z', 'AE', 'CH', 'KH', 'OE', 'SH', 'SHH', 'YA', 'YE', 'YI', 'YO', 'YU', 'ZH'
        );

        return preg_replace($patterns, $replacements, $text);
    }

    public static function setAsPrinted($id_order, $label_file)
    {
        return DB::getInstance()->Execute("
			UPDATE `"._DB_PREFIX_."seur2_order`
			SET `labeled` = 1, label_file='".$label_file."'
			WHERE `id_order` = ".(int)$id_order
		);
    }

    public static function isGeoLabel($id_seur_ccc)
    {
        return false;
    }

    public static function  hasAnyGeoLabel()
    {
	    return false;
    }

    public static function hasFridgeProduct($id_order)
    {
        $seur_order = SeurOrder::getByOrder($id_order);
        if ($seur_order->product==Configuration::get('SEUR2_PICKUP_PRODUCT') ||
            $seur_order->product==Configuration::get('SEUR2_PICKUP_PRODUCT_INT')) {
            return true;
        }
        return false;
    }

    private static function getValidToken(): string
    {
        $idShop = (int) Context::getContext()->shop->id;
        $token  = (string) Configuration::get('SEUR2_TOKEN_API', null, null, $idShop);
        $exp = (int) Configuration::get('SEUR2_TOKEN_API_EXPIRES_AT', null, null, $idShop);

        if (!$token || $exp <= (time() + self::SKEW)) {
            // Evitar carreras si hay peticiones concurrentes
            SeurLib::withLock(function() use ($idShop, &$token) {

                // Relee por si otra petición ya lo renovó mientras esperábamos el lock
                $currentToken = (string) Configuration::get('SEUR2_TOKEN_API', null, null, $idShop);
                $currentExp = (int) Configuration::get('SEUR2_TOKEN_API_EXPIRES_AT', null, null, $idShop);
                if ($currentToken && $currentExp > (time() + self::SKEW)) {
                    $token = $currentToken;
                    return;
                }

                $resp = SeurLib::requestNewToken();
                if (!$resp) {
                    SeurLib::showMessageError(null, 'Error al obtener el token de la API de SEUR');
                    $token = false;
                    return;
                }
                $newToken    = $resp->access_token;
                $expiresAt   = time() + (int) $resp->expires_in;

                // Guarda en Configuration por tienda
                Configuration::updateValue('SEUR2_TOKEN_API', $newToken, false, null, $idShop);
                Configuration::updateValue('SEUR2_TOKEN_API_EXPIRES_AT', $expiresAt, false, null, $idShop);

                $token = $newToken;
            });
        }

        return $token;
    }

    private static function withLock(callable $fn): void
    {
        $lockFile = _PS_CACHE_DIR_.'seur_token.lock';
        $fh = @fopen($lockFile, 'c');
        if ($fh === false) { $fn(); return; } // fallback sin lock

        try {
            flock($fh, LOCK_EX);
            $fn();
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }
    }

    public static function getToken()
    {
        return SeurLib::getValidToken();
    }

    private static function requestNewToken()
    {
        if (!SeurLib::isAPIConfigured()) {
            return false;
        }

        $url = Configuration::get('SEUR2_URLWS_TOKEN');
        //$url =  'https://servicios.apipre.seur.io/pic_token';  // QUITAR !!!!!!!!!!!!!!!!
        $grantType = 'password';
        $clientID = Configuration::get('SEUR2_API_CLIENT_ID');
        $clientSecret = Configuration::get('SEUR2_API_CLIENT_SECRET');
        $username = Configuration::get('SEUR2_API_USERNAME');
        $password = Configuration::get('SEUR2_API_PASSWORD');

        $headers[] = "Accept:*/*";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";

        $data = [
            'grant_type=' . $grantType,
            'client_id=' . $clientID,
            'client_secret=' . $clientSecret,
            'username=' . $username,
            'password=' . $password,
        ];

        $curl_result = SeurLib::sendCurl($url, $headers, $data, "POST", true);

        if (isset($curl_result->access_token)) {
            return $curl_result;
        } elseif ($curl_result == false || isset($curl_result->error)) {
            SeurLib::showMessageError(null, 'TOKEN ERROR: '.$curl_result->error_description);
        }
        return false;
    }

    /**********
     * @param $url string
     * @param $header array
     * @param $data array
     * @param $action string
     * @param $queryparams bool
     * @param $file bool
     *
     * @return string json
     * */
    public static function sendCurl($url, $header, $data, $action, $queryparams = false, $file = false) {
        if (defined('USE_API_PRE') && USE_API_PRE) {
            $url = str_replace('https://servicios.api.seur.io', 'https://servicios.apipre.seur.io', $url);
        }

        $curl = curl_init();

        // Configurar encabezados
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        // Manejo según método HTTP
        switch ($action) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                if ($queryparams) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, implode('&', $data)); // Para el token
                } elseif ($file) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                } else {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;

            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;

            case 'GET':
                if (!empty($data)) {
                    $url .= '?' . http_build_query($data);
                }
                break;

            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $action);
                break;
        }

        // Configurar la URL después de procesar GET
        curl_setopt($curl, CURLOPT_URL, $url);

        // Opciones de seguridad y retorno
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);

        if (json_decode($result) !== null) {
            $result = json_decode($result);
        }

        if (curl_errno($curl)) {
            $errno = curl_errno($curl);
            $error_message = curl_error($curl);
            $error_str = function_exists('curl_strerror') ? curl_strerror($errno) : 'No strerror available';

            SeurLib::showMessageError(
                null,
                "CURL ERROR #$errno: $error_message ($error_str)",
                true
            );

            curl_close($curl);
            return false;
        }

        if (isset($result->error) || isset($result->errors)) {
            SeurLib::log("CURL url: " . $url . "<br>
            header: " . json_encode($header) . "<br>
            params: " . json_encode($data) . "<br>
            result: " . json_encode($result)
            );
        }

        curl_close($curl);
        return $result;
    }

    public static function log($msg) {
        PrestaShopLogger::addLog($msg, 1);
    }

    public static function showMessage($module_instance, $msg, $type, $log)
    {
        $msg = Module::getInstanceByName('seur')->l($msg);

        if (isset(Context::getContext()->controller)) {
            if ($type==0) {
                Context::getContext()->controller->errors[] = $msg;
                Context::getContext()->cookie->errors_messages = $msg;
            }
            if ($type==1) {
                Context::getContext()->controller->confirmations[] = $msg;
                Context::getContext()->cookie->confirmations_messages = $msg;
            }
            if ($type==2) {
                Context::getContext()->controller->warnings[] = $msg;
                Context::getContext()->cookie->warnings_messages = $msg;
            }
        }
        if ($log) self::log($msg);
    }

    public static function showMessageOK($module_instance, $msg, $log=false)
    {
        self::showMessage($module_instance, $msg, 1, $log);
    }

    public static function showMessageWarning($module_instance, $msg, $log=false)
    {
        self::showMessage($module_instance, $msg, 2, $log);
    }

    public static function showMessageError($module_instance, $msg, $log=false)
    {
        self::showMessage($module_instance, $msg, 0, $log);
    }

    public static function isAPIConfigured(){
	    if (!empty(Configuration::get('SEUR2_API_CLIENT_ID')) &&
            !empty(Configuration::get('SEUR2_API_CLIENT_SECRET')) &&
            !empty(Configuration::get('SEUR2_API_USERNAME')) &&
            !empty(Configuration::get('SEUR2_API_PASSWORD'))
        ) {
	        return true;
        }
	    SeurLib::showMessageError(null, "ERROR: Credenciales API no configuradas");
	    return false;
    }

    public static function getTimeTable($timetable) {
        $tb = '';
        if (isset($timetable->weekDays)) {
            foreach ($timetable->weekDays as $day) {
                $tb .= $day->day .' '. $day->openingHours.'<br>';
            }
        }
        return $tb;
    }

    public static function getLastInvoice($id_order) {
        $order_invoices = new PrestaShopCollection('OrderInvoice');
        $order_invoices->where('id_order', '=', $id_order);
        $order_invoices->orderBy('date_add', 'desc');
        $order_invoices->getFirst();
        return $order_invoices;
    }

    public static function invoiceBrexit($id_seur_order)
    {
        $seur_order = new SeurOrder($id_seur_order);

        if ($seur_order->brexit)
            return true;

        $id_order = $seur_order->id_order;
        $order = new Order((int)$id_order);

        $url = Configuration::get('SEUR2_URLWS_BREXIT_INV');

        $file_name = SeurLib::getOrderReference($order);
        $filePath = _PS_MODULE_DIR_ . "seur/files/deliveries_invoices/" . $file_name . ".pdf";
        if (!file_exists($filePath)) {
            //GENERAMOS EL PDF DE LA ULTIMA FACTURA
            $order_invoice_list = SeurLib::getLastInvoice($id_order);

            Hook::exec('actionPDFInvoiceRender', array('order_invoice_list' => $order_invoice_list));
            $pdf = new PDF($order_invoice_list, PDF::TEMPLATE_INVOICE, Context::getContext()->smarty, Context::getContext()->language->id);
            $pdf->filename = $filePath;

            //ALMACENAMOS EN PDF
            $render = false;
            $pdf_renderer = new PDFGenerator((bool)Configuration::get('PS_PDF_USE_CACHE'), "P");
            $pdf_renderer->setFontForLang(Context::getContext()->language->iso_code);
            foreach ($pdf->objects as $object) {
                $pdf_renderer->startPageGroup();
                $template = $pdf->getTemplateObject($object);
                if (!$template) {
                    continue;
                }

                if (empty($pdf->filename)) {
                    $pdf->filename = $template->getFilename();
                    if (count($pdf->objects) > 1) {
                        $pdf->filename = $template->getBulkFilename();
                    }
                }

                $template->assignHookData($object);

                $pdf_renderer->createHeader($template->getHeader());
                $pdf_renderer->createFooter($template->getFooter());
                $pdf_renderer->createPagination($template->getPagination());
                $pdf_renderer->createContent($template->getContent());
                $pdf_renderer->writePage();
                $render = true;

                unset($template);
            }

            if ($render) {
                // clean the output buffer
                if (ob_get_level() && ob_get_length() > 0) {
                    ob_clean();
                }
                $pdf_renderer->render($pdf->filename, 'F');
            }
        }

        $filePDF = curl_file_create($filePath);

        $token = SeurLib::getToken();
        if ($token) {
            $headers[] = "Accept: */*";
            $headers[] = "Authorization: Bearer ".$token;

            $merchant_data = SeurCCC::getMerchantDataByIdCCC($seur_order->id_seur_ccc);

            $data = [
                'businessUnit' => $merchant_data['franchise'],
                'accountNumber' => $merchant_data['ccc'],
                'ref' => SeurLib::getOrderReference($order),
                'file' => $filePDF
            ];

            $curl_result = SeurLib::sendCurl($url, $headers, $data, "POST", false, true);

            if (!$curl_result) {
                return false;
            }
            if (isset($curl_result->status) && $curl_result->status == "OK") {
                SeurLib::setAsBrexit($id_order);
                SeurLib::showMessageOK(null, 'Factura enviada a DigitalDocu', true);
                return true;
            } else {
                SeurLib::showMessageError(null, 'ERROR envío a DigitalDocu: '. $curl_result->errors[0]->detail, true);
                return false;
            }

        } else {
            SeurLib::showMessageError(null, 'ERROR: Acceso API no permitido', true);
            return false;
        }
    }

    public static function invoiceTariff($id_seur_order)
    {
        global $cookie;
        $seur_order = new SeurOrder($id_seur_order);

        if ($seur_order->tariff)
            return true;

        $id_order = $seur_order->id_order;
        $order = new Order((int)$id_order);
        $url = Configuration::get('SEUR2_URLWS_BREXIT_TARIF');

        //OBTENEMOS LA ÚLTIMA LA FACTURA
        $order_invoices = SeurLib::getLastInvoice($id_order);
        foreach ($order_invoices as $order_invoice) { } //solo quiero quedarme con el objeto para usarlo después

        $token = SeurLib::getToken();
        if ($token) {
            $headers[] = "Accept: */*";
            $headers[] = "Content-Type: application/json";
            $headers[] = "Authorization: Bearer ".$token;

            $merchant_data = SeurCCC::getMerchantDataByIdCCC($seur_order->id_seur_ccc);

            $cont = 0;
            $departures = [];
            foreach ($order->getProducts() as $product) {
                $cont++;
                $departures[] = [
                    'itemId' => $cont,
                    'itemNumber' => $product['product_quantity'],
                    'itemDescription' => $product['product_name'],
                    'itemValuate' => $product['total_price_tax_incl'],
                    'itemCountry' => $merchant_data['country'],
                    'itemWeight' => $product['product_weight']*1000,
                    'taric' => Configuration::get('SEUR2_TARIC')
                ];
            }

            $currency = new Currency($order->id_currency);
            $data = [
                'businessUnit' =>$merchant_data['franchise'],
                'accountNumber' => $merchant_data['ccc'],
                'customerReference' => SeurLib::getOrderReference($order),
                'customerMerchandiseType' => 'C',
                'recipientCodeType' => 'C',
                'rEORI' => Configuration::get('SEUR2_R_EORI'),
                'dEORI' => Configuration::get('SEUR2_D_EORI'),
                'incoterm' => '',
                'invoiceClientNumber' => $order_invoice->getInvoiceNumberFormatted($cookie->id_lang),
                'invoiceClientDate' => date('d/m/Y', strtotime($order_invoice->date_add)),
                'invoiceClientValuate' => number_format($order_invoice->total_paid_tax_incl, 3),
                'invoiceClientBadge' => $currency->iso_code,
                'departures' => $departures
            ];

            $curl_result = SeurLib::sendCurl($url, $headers, $data, "POST");

            if (!$curl_result) {
                return false;
            }
            if (isset($curl_result->status) && $curl_result->status == "OK") {
                seurLib::setAsTariff($id_order);
                SeurLib::showMessageOK(null, 'Partidas aduaneras enviadas', true);
                return true;
            } else {
                SeurLib::showMessageError(null, 'ERROR Partidas aduaneras: '. $curl_result->errors[0]->detail, true);
                return false;
            }

        } else {
            SeurLib::showMessageError(null, 'ERROR: Acceso API no permitido', true);
            return false;
        }
    }

    public static function setAsBrexit($id_order)
    {
        return DB::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'seur2_order`
			SET `brexit` = 1, brexit_date = current_time()
			WHERE `id_order` = "'.(int)$id_order.'"
		');
    }

    public static function setAsTariff($id_order)
    {
        return DB::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'seur2_order`
			SET `tariff` = 1, tariff_date = current_time()
			WHERE `id_order` = "'.(int)$id_order.'"
		');
    }

    public static function isBrexit($id_order)
    {
        return DB::getInstance()->getValue('
			SELECT `brexit`
			FROM `'._DB_PREFIX_.'seur2_order`
			WHERE `id_order` = "'.(int)$id_order.'"
		');
    }

    public static function isTariff($id_order)
    {
        return DB::getInstance()->getValue('
			SELECT `tariff`
			FROM `'._DB_PREFIX_.'seur2_order`
			WHERE `id_order` = "'.(int)$id_order.'"
		');
    }

    public static function getOrderReference($order) {
        return (Configuration::get('SEUR2_SETTINGS_LABEL_REFERENCE_TYPE')==2 ? $order->id : $order->reference);
    }

    public static function getFormatedOrderReference($order) {
        $seur_order = SeurOrder::getByOrder($order->id);
        $merchant = SeurCCC::getMerchantDataByIdCCC($seur_order->id_seur_ccc);
        $un = str_pad($merchant['franchise'], 3, "0", STR_PAD_LEFT);
        $ccc = str_pad($merchant['ccc'], 7, "0", STR_PAD_LEFT);
        $customer_reference = self::getOrderReference($order);
        return $un.$ccc.$customer_reference;
    }

    public static function AddCOD(Order $order)
    {
        if (strcmp($order->module, 'seurcashondelivery') == 0 || SeurLib::AddAllSeurCODPayments()) {
            return true;
        }
        return false;
    }

    public static function AddCODData($label_data)
    {
        if ((isset($label_data['reembolso']) && ( !SeurLib::isInternationalShipping($label_data['iso']))) || SeurLib::AddAllSeurCODPayments()) {
            return true;
        }
        return false;
    }

    public static function AddAllSeurCODPayments()
    {
        return (defined('MODULE_SEUR_COD_4ALL_PAYMENTS') && MODULE_SEUR_COD_4ALL_PAYMENTS);
    }

    public static function getAllSeurCODPayments($orderReference)
    {
        $sql = "SELECT sum(amount) as total_paid
            FROM " . _DB_PREFIX_ . "order_payment
            where payment_method = '".self::CODPaymentName."' and order_reference = '".$orderReference."'";
        $total_paid = Db::getInstance()->getValue($sql);
        return $total_paid > 0 ? $total_paid : 0;
    }

    public static function removeAccents($text)
    {
        return str_replace(
            array('á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'),
            array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', 'u', 'U'),
            $text
        );
    }

    static function getLabelFileName($order, $label_file) {
        if (!$label_file) {
            $label_file = SeurLib::existLabelFile($order->reference);
        }
        if (!$label_file) {
            $label_file = SeurLib::existLabelFile($order->id);
        }
        if (!$label_file) {
            $seur_order = SeurOrder::getByOrder($order->id);
            $merchant = SeurCCC::getMerchantDataByIdCCC($seur_order->id_seur_ccc);
            $un = str_pad($merchant['franchise'], 3, "0", STR_PAD_LEFT);
            $ccc = str_pad($merchant['ccc'], 7, "0", STR_PAD_LEFT);
            $label_file = SeurLib::existLabelFile($un.$ccc.$order->reference);
            if (!$label_file) {
                $label_file = SeurLib::existLabelFile($un.$ccc.$order->id);
            }
        }
        if ($label_file) {
            $query = "UPDATE `"._DB_PREFIX_."seur2_order` SET labeled=1, label_file='".$label_file."' WHERE id_order=".$order->id;
            Db::getInstance()->execute($query);
            return $label_file;
        }
        return false;
    }

    static function isPdf() {
        return (Configuration::get('SEUR2_SETTINGS_PRINT_TYPE') == 1 || Configuration::get('SEUR2_SETTINGS_PRINT_TYPE') == 3);
    }

    static function getLabelType() {
        return (self::isPdf()?'pdf':'txt');
    }

    static function existLabelFile($filename) {
        $directory = _PS_MODULE_DIR_ . 'seur/files/deliveries_labels/';

        if (file_exists($directory . $filename .'.'. self::getLabelType())) {
            return  $filename .'.'. self::getLabelType();
        }
        return false;
    }

    function updateFieldLabelFile() {
        /* Rellenar el campo label_file con el nombre del fichero correspondiente */
        $sql = "SELECT * FROM `"._DB_PREFIX_."seur2_order` WHERE labeled = 1";
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        foreach($result as $seur_order)
        {
            $order = new Order($seur_order['id_order']);
            SeurLib::getLabelFileName($order, $seur_order['label_file']);
        }
    }

    public static function isCODPayment($order) {
        $payment = $order->module.'-'.$order->payment;
        if ((strpos($payment, 'cod') !== false ||
             strpos($payment, 'cashondelivery') !== false ||
             strpos($payment, 'reembolso') !== false ))
        {
            return true;
        }
        return false;
    }

    public static function calculateCODAmount($order) {
        $fake_cart = new Cart((int) $order->id_cart);
        $cod = new SeurCashOnDelivery();
        return $cod->calculateCartAmount($fake_cart);
    }

    public static function sanitize($string) {
        return str_replace('&', '', $string);
    }

    /**
     * Get delivery date for the current shipment:
     * - Sunday through Friday: next day
     * - Saturday: Monday
     *
     * @return string
     */
    public static function getDeliveryDate()
    {
        $deliveryDate = new DateTime('tomorrow');
        $deliveryDay = strtolower(date('l', $deliveryDate->getTimestamp()));
        if ($deliveryDay == 'sunday') {
            $deliveryDate->add(new \DateInterval('P1D'));
        }
        return $deliveryDate->format('d/m/Y');
    }

    /**
     * @param $order
     * @return null
     */
    public static function getShipmentType($id_order)
    {
        $orderProductTypes = [];
        $type = new ProductType();
        $productTypes = $type->getOptions();
        $isOrderFood = true;
        $order = new OrderCore($id_order);
        foreach ($order->getProducts() as $product) {
            /*if ($product['product_type'] !== 'standard') {
                // Ignore complex or virtual products
                continue;
            }*/
            if ($seurProductType = SeurLib::getSeurProductType($product)) {
                $orderProductTypes[$seurProductType] = true;
                if ($seurProductType == $productTypes[ProductType::PRODUCT_TYPE_OTHER]) {
                        $isOrderFood = false;
                }
            } else {
                $orderProductTypes[$productTypes[ProductType::PRODUCT_TYPE_OTHER]] = true;
                $isOrderFood = false;
            }
        }
        $orderProductTypes = array_keys($orderProductTypes);
        if (count($orderProductTypes) === 1) {
            $shipmentType = reset($orderProductTypes);
        } elseif ($isOrderFood) {
            $shipmentType = $productTypes[ProductType::PRODUCT_TYPE_FOOD_OTHER];
        } else {
            $shipmentType = $productTypes[ProductType::PRODUCT_TYPE_OTHER];
        }

        return $shipmentType;
    }

    static function getSeurProductType($product) {
        $sql = "SELECT value
                FROM " . _DB_PREFIX_ . "feature_product fp
                INNER JOIN " . _DB_PREFIX_ . "feature_lang fl
                    ON fl.id_feature = fp.id_feature
                    AND fl.name = '" . ProductType::PRODUCT_TYPE_ATTRIBUTE_CODE . "'
                INNER JOIN " . _DB_PREFIX_ . "feature_value_lang fvl
                    ON fvl.id_feature_value = fp.id_feature_value
                WHERE id_product = " . (int)$product['product_id'] . "
                AND fvl.id_lang = " . (int)Context::getContext()->language->id;
        return DB::getInstance()->getValue($sql);
    }

    static function cleanPhone($phone) {
        $phone = preg_replace('/[\s+\-\.\(\)\/]/', '', $phone);
        $phone = preg_replace('/^0+/', '', $phone);
        return $phone;
    }

    static function getServicesTypes() {
        $sql = "SELECT id_seur_services_type, name FROM `" . _DB_PREFIX_ . "seur2_services_type` ";
        return Db::getInstance()->executeS($sql);
    }

    public static function getCustomerAddressId($customerId, $criteria): int
    {
        $query = new DbQuery();
        $query->select('id_address');
        $query->from('address');
        $query->where('id_customer = ' . (int)$customerId);
        foreach ($criteria as $field => $value) {
            $query->where("$field = '" . pSQL($value) . "'");
        }
        $query->orderBy('id_address DESC');
        return (int)Db::getInstance()->getValue($query);
    }

    public static function ShipmentDataUpdated($seur_order_old, $seur_order)
    {
        return $seur_order_old->firstname != $seur_order->firstname
            || $seur_order_old->lastname != $seur_order->lastname
            || $seur_order_old->phone != $seur_order->phone
            || $seur_order_old->phone_mobile != $seur_order->phone_mobile
            || $seur_order_old->dni != $seur_order->dni
            || $seur_order_old->other != $seur_order->other
            || $seur_order_old->address1 != $seur_order->address1
            || $seur_order_old->address2 != $seur_order->address2;
    }

    public static function PackagesDataUpdated($packages_old, $packages)
    {
        return $packages_old != $packages;
    }

    public static function updateOrderAddress($seur_order)
    {
        $address = new Address();
        $address->id_customer = (int)$seur_order->id_customer;
        $address->id_country = (int)$seur_order->id_country;
        $address->id_state = (int)$seur_order->id_state;
        $address->alias = 'Seur';
        $address->company = $seur_order->company;
        $address->lastname = $seur_order->lastname;
        $address->firstname = $seur_order->firstname;
        $address->address1 = $seur_order->address1;
        $address->address2 = $seur_order->address2;
        $address->postcode = $seur_order->postcode;
        $address->city = $seur_order->city;
        $address->phone = $seur_order->phone;
        $address->phone_mobile = $seur_order->phone_mobile;
        $address->dni = $seur_order->dni;
        $address->other = $seur_order->other;
        $address->add();

        $order = new Order($seur_order->id_order);
        $order->id_address_delivery = $address->id;
        $order->save();

        $seur_order->id_address_delivery = $address->id;
        $seur_order->save();
    }

    public static function updateSeurOrderWithParcels($seur_order, $response) {
        // Verificar que la respuesta contiene los datos esperados
        if (!isset($response->ecbs) || !isset($response->parcelNumbers)) {
            SeurLib::showMessageError(null, 'Error 1', true);
            return false;
        }

        try {
            // Concatenar los valores de ecbs y parcelNumbers en formato separado por "-"
            $seur_order->ecbs = implode('-', $response->ecbs);
            $seur_order->parcelNumbers = implode('-', $response->parcelNumbers);

            // Guardar los cambios en la base de datos
            if (!$seur_order->save()) {
                SeurLib::showMessageError(null, 'Error 2', true);
                return false;
            }

            SeurLib::showMessageOK(null, "Parcels Added Successfully");
            return true;

        } catch (PrestaShopException $e) {
            SeurLib::showMessageError(null, 'Error 3' .$e->getMessage(), true);

            return false;
        }
    }

    public static function getBaseLink()
    {
        return Context::getContext()->shop->getBaseURL(Configuration::get('PS_SSL_ENABLED'));
    }

    public static function getValue($key, $default_value = false) {
        $value = Tools::getValue($key, $default_value);
        if (is_string($value)) {
            return trim($value);
        }
        return $value;
    }
}