<?php
/**
 * Class Pushkarcreations_Core
 */
abstract class Pushkarcreations_Core{

    /**
     * Merge user defined arguments into defaults array.
     *
     * @param string|array $args Value to merge with $defaults
     * @param array $defaults Array that serves as the defaults.
     * @return array Merged user defined values with defaults.
     */
    public function mage_parse_args( $args, $defaults = '' ) {
        if ( is_object( $args ) )
            $r = get_object_vars( $args );
        elseif ( is_array( $args ) )
            $r =& $args;

        if ( is_array( $defaults ) )
            return array_merge( $defaults, $r );
        return $r;
    }

    /**
     * Insert standard product values used in magento
     * @param  array $stdFields
     * @return array return with default values inserted
     */
    public function getDefaultData( $stdFields ){
        if ($stdFields) {
            foreach ($stdFields as $key => $value) {
                switch ($value) {
                    case 'attribute_set':
                        $defData[$value] = "Default";
                        break;
                    case 'store':
                        $defData[$value] = "admin";
                        break;
                    case 'type':
                        $defData[$value] = "simple";
                        break;
                    case 'websites':
                        $defData[$value] = "base";
                        break;
                    case 'enable_googlecheckout':
                        $defData[$value] = 1;
                        break;
                    case 'has_options':
                        $defData[$value] = 1;
                        break;
                    case 'magazine_cover':
                        $defData[$value] = 'no_selection';
                        break;
                    case 'options_container':
                        $defData[$value] = 'Block after Info Column';
                        break;
                    case 'status':
                        $defData[$value] = 1;
                        break;
                    case 'tax_class_id':
                        $defData[$value] = 2;
                        break;
                    case 'visibility':
                        $defData[$value] = 4;
                        break;
                    case 'min_qty':
                        $defData[$value] = 0;
                        break;
                    case 'use_config_min_qty':
                        $defData[$value] = 1;
                        break;
                    case 'is_qty_decimal':
                        $defData[$value] = 0;
                        break;
                    case 'backorders':
                        $defData[$value] = 0;
                        break;
                    case 'use_config_backorders':
                        $defData[$value] = 1;
                        break;
                    case 'min_sale_qty':
                        $defData[$value] = 1;
                        break;
                    case 'use_config_min_sale_qty':
                        $defData[$value] = 1;
                        break;
                    case 'max_sale_qty':
                        $defData[$value] = 0;
                        break;
                    case 'use_config_max_sale_qty':
                        $defData[$value] = 1;
                        break;
                    case 'is_in_stock':
                        $defData[$value] = 1;
                        break;
                    case 'use_config_notify_stock_qty':
                        $defData[$value] = 1;
                        break;
                    case 'manage_stock':
                        $defData[$value] = 1;
                        break;
                    case 'use_config_manage_stock':
                        $defData[$value] = 1;
                        break;
                    case 'use_config_qty_increments':
                        $defData[$value] = 1;
                        break;
                    case 'qty_increments':
                        $defData[$value] = 0;
                        break;
                    case 'use_config_enable_qty_increments':
                        $defData[$value] = 1;
                        break;
                    case 'enable_qty_increments':
                        $defData[$value] = 1;
                        break;
                    case '_media_attribute_id':
                        $defData[$value] = 88;
                        break;
                    case '_media_position':
                        $defData[$value] = 1;
                        break;
                    case '_media_is_disabled':
                        $defData[$value] = 0;
                        break;
                    default:
                        $defData[$value] = null;
                        break;
                }
            }
            return $defData;
        }
    }

    /**
     * Get Database connection obj
     * @param  string $type [description]
     * @return object       [description]
     */
    public function _getConnection($type = 'core_read'){
        return Mage::getSingleton('core/resource')->getConnection($type);
    }

    /**
     * Get table name form database - it supporst magento standards
     * @param  string $tableName [description]
     * @return object            [description]
     */
    public function _getTableName($tableName = 'catalog_category_csv_mapping'){
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }

    public function skipProductsFromCategories(){
        return array();
    }

}
