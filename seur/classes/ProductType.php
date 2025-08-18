<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Maria Jose Santos <mariajose.santos@ebolution.com>
 * @copyright 2023 Seur Transporte
 * @license https://seur.com/ Proprietary
 */
class ProductType
{
    const PRODUCT_TYPE_ATTRIBUTE_CODE = 'seur_product_type';
    const PRODUCT_TYPE_FOOD_MEAT = 1; // Alimentación/carne
    const PRODUCT_TYPE_FOOD_FISH = 2; // Alimentación/pescado
    const PRODUCT_TYPE_FOOD_DAIRY = 3; // Alimentación/lácteos
    const PRODUCT_TYPE_FOOD_PROCESSED = 4; // Alimentación/preparados
    const PRODUCT_TYPE_FOOD_OTHER = 5; // Alimentación/otros
    const PRODUCT_TYPE_OTHER = 6; // Otros/no alimentación


    public function __construct()
    {
    }
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PRODUCT_TYPE_FOOD_MEAT, 'label' => 'Alimentación/Carne'],
            ['value' => self::PRODUCT_TYPE_FOOD_FISH, 'label' => 'Alimentación/Pescado'],
            ['value' => self::PRODUCT_TYPE_FOOD_DAIRY, 'label' => 'Alimentación/Lácteos'],
            ['value' => self::PRODUCT_TYPE_FOOD_PROCESSED, 'label' => 'Alimentación/Preparados'],
            ['value' => self::PRODUCT_TYPE_FOOD_OTHER, 'label' => 'Alimentación/Otros'],
            ['value' => self::PRODUCT_TYPE_OTHER, 'label' => 'Otros/No alimentación']
        ];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    public function install() {
        $db = Db::getInstance();
        $link = $db->getLink();

        // Iniciar transacción según el tipo de conexión
        if ($link instanceof \PDO) {
            $link->beginTransaction();
        } elseif ($link instanceof \mysqli) {
            $link->begin_transaction();
        }

        try {
            // Comprobar si la característica ya existe
            $feature_id = $this->findFeature(self::PRODUCT_TYPE_ATTRIBUTE_CODE);
            if (!$feature_id) {
                // Si no existe, la creamos
                $feature_id = FeatureCore::addFeatureImport(self::PRODUCT_TYPE_ATTRIBUTE_CODE);

                // Añadir los valores
                $values = $this->getOptions();
                foreach ($values as $value) {
                    FeatureValueCore::addFeatureValueImport($feature_id, $value);
                }
            } else {
                // Verificar si los valores ya existen
                $values = $this->getOptions();
                foreach ($values as $value) {
                    $valueExists = $this->existsFeatureValue($feature_id, $value);
                    if (!$valueExists) {
                        FeatureValueCore::addFeatureValueImport($feature_id, $value);
                    }
                }
            }

            // Confirmar transacción
            if ($link->inTransaction()) {
                $link->commit();
            }
            return true;

        } catch (Exception $e) {
            // Loguear errores
            SeurLib::log('ADD SEUR PRODUCT_TYPE FEATURE: ' . $e->getMessage());
            if ($link->inTransaction()) {
                $link->rollback();
            }
            return false;
        }
    }

    private function findFeature(string $name)
    {
        $defaultLangId = (int)Configuration::get('PS_LANG_DEFAULT');
        $features = FeatureCore::getFeatures($defaultLangId);
        foreach($features as $feature) {
            if ($feature['name'] === $name) {
                return intval($feature['id_feature']);
            }
        }

        return null;
    }

    public function existsFeatureValue($feature_id, $value) {
        $sql = "SELECT COUNT(*)
            FROM " . _DB_PREFIX_ . "feature_value_lang fvl
            INNER JOIN " . _DB_PREFIX_ . "feature_value fv ON fvl.id_feature_value = fv.id_feature_value
            WHERE fv.id_feature = " . (int)$feature_id . "
              AND fvl.value = '" . pSQL($value) . "'";

        return (bool)Db::getInstance()->getValue($sql);
    }


}
