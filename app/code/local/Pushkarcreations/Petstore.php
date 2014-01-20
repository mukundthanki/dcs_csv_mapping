<?php
/**
 * Class Pushkarcreations_Petstore
 */
class Pushkarcreations_Petstore extends Pushkarcreations_Core{

    const VENDOR_CODE = 'petstore';

    const VENDOR_PREFIX = 'PS';

    /**
     * Making an array of keys which will replacing to standard fields,
     * Use same key/value pair to remove while making csv.
     * @return array Replace key/value pair of vendor fields and standard magento fields
     */
    public function replaceKeysWithStandard(){
        return array(
            'Item ID'   => 'sku',
            'Manufacturer'  => 'manufacturer',
            'Product Name'  => 'name',
            'Product Description'   => 'short_description',
            'Long Description'  => 'description',
            'Cost'  => 'cost',
            'SRP'   => 'price',
            'Minimum Advertising Price (MAP)'   => 'Minimum Advertising Price (MAP)',
            'Notes'   => 'Notes',
            'UPC'   => 'upc',
            'Image Name'    => 'image',
            'Primary Category'  => 'categories',
            'Secondary Category'    => 'Secondary Category',
            'Tertiary Category' => 'Tertiary Category',
            'Shipping Weight'   => 'Shipping Weight',
            'Ship Length'   => 'Ship Length',
            'Ship Width'    => 'Ship Width',
            'Ship Height'   => 'Ship Height',
            'UPS Ship Weight'   => 'UPS Ship Weight',
            'UPS Handling Fee'  => 'UPS Handling Fee',
            'UPS Expedited Weight'  => 'UPS Expedited Weight',
            'Item Weight'   => 'weight',
            'Item Length'   => 'Item Length',
            'Item Width'    => 'Item Width',
            'Item Height'   => 'Item Height',
            'QOH'   => 'qty',
            'QPO'   => 'QPO',
            'Usage' => 'Usage',
            'Manufacturing Country' => 'country_of_manufacture',
            'MPN'   => 'MPN',
        );
    }

    /**
     * Insert data to fields from vendor csv as per mapping.
     * @param  array $data
     * @return array $data With additional keys and values
     */
    public function formatFieldsPerMapping($data){

        // Insert Prefix to sku to avoid conflict
        $data['Item ID'] = self::VENDOR_PREFIX.$data['Item ID'];

        // Map categories to one field which supports magmi
        $parentcat = implode("-", array_map('trim', explode("/", $data['Primary Category'])));//str_replace("/", "-", trim($data['Primary Category']));
        $subcat = implode("-", array_map('trim', explode("/", $data['Secondary Category'])));//str_replace("/", "-", trim($data['Secondary Category']));
        $subsubcat = implode("-", array_map('trim', explode("/", $data['Tertiary Category'])));//str_replace("/", "-", trim($data["Tertiary Category"]));
        $catPath = $parentcat."/".$subcat."/".$subsubcat;

        $catMap = $this->categoryMappingArray();

        if( array_key_exists($catPath, $catMap) ){
            $data['Primary Category'] = $catMap[$catPath];
        }/*elseif( $this->_searchForParentCat($parentcat) ){
            $data['Primary Category'] = $parentcat;
        }*/else{
            $data['Primary Category'] = $parentcat.";;".$parentcat."/".$subcat;
        }

        // Edit image field to support magmi script and directconnect workflow
        $data['Image Name'] = "+images/".$data['Image Name'];

        // set product out of stock if qty is 0
        if( $data['QOH'] == 0 )
            $data['is_in_stock'] = 0;

        // Mapping Missing Fields in target csv
        $data['image_label'] = $data['Product Name'];

        // Ignore Fields which we don't want to update
        $data['meta_description'] = "__MAGMI_IGNORE__";
        $data['meta_keyword'] = "__MAGMI_IGNORE__";
        $data['meta_title'] = "__MAGMI_IGNORE__";
        $data['news_from_date'] = "__MAGMI_IGNORE__";
        $data['news_to_date'] = "__MAGMI_IGNORE__";
        $data['description'] = "__MAGMI_IGNORE__";
        $data['short_description'] = "__MAGMI_IGNORE__";

        $data['msrp']   = $data['SRP'];
        $data['small_image']    = $data['Image Name'];
        $data['small_image_label']  = $data['Product Name'];
        $data['special_price']  = number_format($data['Cost'] + ( $data['Cost']*0.25 ), 2);
        $data['thumbnail']  = $data['Image Name'];
        $data['thumbnail_label']    = $data['Product Name'];

        return $data;
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
