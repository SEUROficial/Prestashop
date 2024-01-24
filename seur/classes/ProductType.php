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

        if ($link instanceof \PDO) {
            $link->beginTransaction();
        } elseif ($link instanceof \mysqli) {
            $link->begin_transaction();
        }
        try {
            $feature_id = FeatureCore::addFeatureImport(self::PRODUCT_TYPE_ATTRIBUTE_CODE);
            $values = $this->getOptions();
            foreach ($values as $value) {
                FeatureValueCore::addFeatureValueImport($feature_id, $value);
            }
            if ($link->inTransaction()) {
                $link->commit();
            }
            return true;
        } catch (Exception $e) {
            SeurLib::log('ADD SEUR PRODUCT_TYPE FEATURE: '.$e->getMessage());
            if ($link->inTransaction()) {
                $link->rollback();
            }
            return false;
        }
    }
}
