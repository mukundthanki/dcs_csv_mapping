<?php
/**
 * Class Pushkarcreations_Xsdepot
 */
class Pushkarcreations_Xsdepot extends Pushkarcreations_Core{

    const VENDOR_CODE = 'xsdepot';

    const VENDOR_PREFIX = 'XS';

    /**
     * Making an array of keys which will replacing to standard fields,
     * Use same key/value pair to remove while making csv.
     * @return array Replace key/value pair of vendor fields and standard magento fields
     */
    public function replaceKeysWithStandard(){
        return array(
            'TitleID'   => 'sku',
            'Title'  => 'name',
            'Price' => 'cost',
            'Format'  => 'Format',
            'ShortDescription'   => 'short_description',
            'Notes'   => 'Notes',
            'Age'   => 'Age',
            'Publisher'   => 'manufacturer',
            'InStock'   => 'qty',
            'UPC'   => 'upc',
            'Platform'  => 'Platform',
            'ImageURL'  => 'ImageURL',
            'ThumbnailURL'  => 'ThumbnailURL',
            'LongDescription'   => 'description',
            'MSRP'  => 'msrp',
            'ShippingCharge'  => 'ShippingCharge',
            'MainCategory'  => 'categories',
            'Category'    => 'Category',
            'Subcategory' => 'Subcategory',
            'Status'   => 'Status',
            'StatusChangeDate'   => 'StatusChangeDate',
            'Oversize'    => 'Oversize',
            'ManufacturerSKU'   => 'ManufacturerSKU',
            'AmazonASIN'    => 'AmazonASIN',
            'Condition' => 'Condition',
            'LowestTierPrice'   => 'LowestTierPrice',
            'QtyForLowestTierPrice' => 'QtyForLowestTierPrice',
            'LargeImageURL' => 'image',
            'Weight'    => 'weight'
        );
    }

    /**
     * Insert data to fields from vendor csv as per mapping.
     * @param  array $data
     * @return array $data With additional keys and values
     */
    public function formatFieldsPerMapping($data){

        // Insert Prefix to sku to avoid conflict
        $data['TitleID'] = self::VENDOR_PREFIX.$data['TitleID'];

        // Map categories to one field which supports magmi
        $parentcat = implode("-", array_map( 'trim', explode("/", trim($data['MainCategory'])) ) );
        $subcat = implode("-", array_map( 'trim', explode("/", trim($data['Category'])) ) );
        $subsubcat = implode("-", array_map( 'trim', explode("/", trim($data['Subcategory'])) ) );
        //$data['MainCategory'] = $parentcat.";;".$parentcat."/".$subcat;
        $catPath = $parentcat."/".$subcat;

        $catMap = $this->categoryMappingArray();

        if( array_key_exists($catPath, $catMap) ){
            $data['MainCategory'] = $catMap[$catPath];
        }/*elseif( $this->_searchForParentCat($parentcat) ){
            $data['MainCategory'] = $parentcat;
        }*/else{
            $data['MainCategory'] = $parentcat.";;".$parentcat."/".$subcat;
        }

        // set product out of stock if qty is 0
        if( $data['InStock'] == 0 )
            $data['is_in_stock'] = 0;

        // Edit image field to support magmi script
        $data['LargeImageURL'] = "+".$data['LargeImageURL'];

        // Mapping Missing Fields in target csv
        $data['image_label'] = $data['Title'];

        // Ignore Fields which we don't want to update
        $data['meta_description'] = "__MAGMI_IGNORE__";
        $data['meta_keyword'] = "__MAGMI_IGNORE__";
        $data['meta_title'] = "__MAGMI_IGNORE__";
        $data['news_from_date'] = "__MAGMI_IGNORE__";
        $data['news_to_date'] = "__MAGMI_IGNORE__";
        $data['description'] = "__MAGMI_IGNORE__";
        $data['short_description'] = "__MAGMI_IGNORE__";

        $data['price']   = $data['MSRP'];
        $data['small_image']    = $data['LargeImageURL'];
        $data['small_image_label']  = $data['Title'];
        $data['thumbnail']  = $data['LargeImageURL'];
        $data['thumbnail_label']    = $data['Title'];
        $data['special_price']  = number_format($data['Price'] + 2 + ( $data['Price']*0.25 ), 2 );

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
