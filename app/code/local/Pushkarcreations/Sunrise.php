<?php
/**
 * Class Pushkarcreations_Sunrise
 */
class Pushkarcreations_Sunrise extends Pushkarcreations_Core{

    const VENDOR_CODE = 'sunrise';

    const VENDOR_PREFIX = 'SR';

    /**
     * Making an array of keys which will replacing to standard fields,
     * Use same key/value pair to remove while making csv.
     * @return array Replace key/value pair of vendor fields and standard magento fields
     */
    public function replaceKeysWithStandard(){
        return array(
            'Item_No'   => 'sku',
            'Brand'  => 'manufacturer',
            'Model' => 'Model',
            'Short Description'  => 'name',
            'Long Description'  => 'description',
            'Retail Price'   => 'msrp',
            'Wholesale Price'   => 'special_price',
            'Gold Member Price'   => 'cost',
            'PKG'   => 'PKG',
            'UPC'   => 'upc',
            'Shipping'  => 'Shipping',
            'Image'    => 'image',
            'Gold Member Shipping'  => 'Gold Member Shipping',
            'Main Category'  => 'categories',
            'Subcategory'    => 'Subcategory',
            'Subcategory 2' => 'Subcategory 2',
            'In Stock'   => 'In Stock',
            'Qty in Stock'   => 'qty',
            'Discontinued'   => 'Discontinued',
            'ETA'    => 'ETA',
            'Thumbnail'   => 'Thumbnail',
            'MAP_Price'   => 'MAP_Price',
            'Weight'   => 'weight',
            'Reg_Sale_Price'   => 'Reg_Sale_Price',
            'Gold_Sale_Price'    => 'Gold_Sale_Price',
            'Sale_Expires'   => 'Sale_Expires',
            'Added_Date'   => 'Added_Date',
            'Dot_Color' => 'Dot_Color',
        );
    }

    /**
     * Insert data to fields from vendor csv as per mapping.
     * @param  array $data
     * @return array $data With additional keys and values
     */
    public function formatFieldsPerMapping($data){

        // Insert Prefix to sku to avoid conflict
        $data['Item_No'] = self::VENDOR_PREFIX.$data['Item_No'];

        // Map categories to one field which supports magmi
        $parentcat = implode("-", array_map('trim', explode("/", $data['Main Category'])));//str_replace("/", "-", trim($data['Main Category']));
        $subcat = implode("-", array_map('trim', explode("/", $data['Subcategory'])));//str_replace("/", "-", trim($data['Subcategory']));
        $subsubcat = implode("-", array_map('trim', explode("/", $data['Subcategory 2'])));//str_replace("/", "-", trim($data['Subcategory 2']));
        //$data['Main Category'] = $parentcat.";;".$parentcat."/".$subcat;
        $catPath = $parentcat."/".$subcat;

        $catMap = $this->categoryMappingArray();

        // set product out of stock if qty is 0
        if( $data['Qty in Stock'] == 0 )
            $data['is_in_stock'] = 0;

        if( array_key_exists($catPath, $catMap) ){
            $data['Main Category'] = $catMap[$catPath];
        }/*elseif( $this->_searchForParentCat($parentcat) ){
            $data['Main Category'] = $parentcat;
        }*/else{
            $data['Main Category'] = $parentcat.";;".$parentcat."/".$subcat;
        }

        // Edit image field to support magmi script
        $data['Image'] = "+".$data['Image'];

        // Mapping Missing Fields in target csv
        $data['image_label'] = $data['Short Description'];
        $data['short_description'] = $data['Short Description'];

        // Ignore Fields which we don't want to update
        $data['meta_description'] = "__MAGMI_IGNORE__";
        $data['meta_keyword'] = "__MAGMI_IGNORE__";
        $data['meta_title'] = "__MAGMI_IGNORE__";
        $data['news_from_date'] = "__MAGMI_IGNORE__";
        $data['news_to_date'] = "__MAGMI_IGNORE__";
        $data['description'] = "__MAGMI_IGNORE__";
        $data['short_description'] = "__MAGMI_IGNORE__";

        $data['price']   = $data['Retail Price'];
        $data['small_image']    = $data['Image'];
        $data['small_image_label']  = $data['Short Description'];
        $data['thumbnail']  = $data['Image'];
        $data['thumbnail_label']    = $data['Short Description'];

        return $data;
    }

    public function skipProductsFromCategories(){
        return array(
                'DO NOT IMPORT;;DO NOT IMPORT/DO NOT IMPORT'
            );
    }

    private function _searchForParentCat( $parentcat ){
        if( $this->categoryMappingArray() ){
            foreach($this->categoryMappingArray() as $key => $value){
                $arr = explode("/", $key);
                if( $arr[0] == $parentcat ){
                    return true;
                }
            }
        }else{
            return false;
        }
    }

    public function categoryMappingArray(){
        $readConn = $this->_getConnection();
        $tableName = $this->_getTableName();

        $sql = "SELECT vendor_cat_path, dcs_cat_path FROM " . $tableName . " WHERE vendor_name = '".self::VENDOR_CODE."'";
        $data = $readConn->fetchPairs($sql, array('vendor_cat_path', 'dcs_cat_path'));
        if($data){
            return $data;
        }else{
            return false;
        }
    }
}
