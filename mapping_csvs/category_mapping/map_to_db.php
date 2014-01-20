<?php

    // Starting of execution of script

    require_once '../../app/Mage.php';
    //require_once 'includes/Pushkarcreations_Petstore.php';

    Mage::setIsDeveloperMode(true);
    umask(0);
    Mage::app('admin');
    Mage::register('isSecureArea', 1);
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

    set_time_limit(0);
    ini_set('display_errors', 1);
    ini_set('memory_limit','1024M');

    echo "<pre>";

    $csv = new Varien_File_Csv();
    $vendorCode = 'xsdepot';

    $default = $csv->getData($vendorCode.'_mapping.csv');

    function _getConnection($type = 'core_read'){
        return Mage::getSingleton('core/resource')->getConnection($type);
    }

    function _getTableName($tableName){
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }

    array_shift($default);
    array_shift($default);

    $writeConn = _getConnection('core_write');
    $readConn = _getConnection();
    $tableName = _getTableName('catalog_category_csv_mapping');

    //$dcs_cat_path = array();
    $vendor = array();

    foreach ($default as $key => $value) {

        /**
         * xsdepot_output file mapping code
         * @var [type]
         */
        /*$vendor = explode(";;", $value[0]);
        $dcs = explode(";;", $value[1]);

        $vendorValue = replaceChar(explode("/", $vendor[1]));
        $dcsValue = replaceChar(explode("/", $dcs[1]));

        /*$vendor_cat_path = trim($vendorValue[0])."/".trim($vendorValue[1]);
        if (isset($dcsValue[2]) && $dcsValue[2] != "") {
            $dcs_cat_path[$vendor_cat_path]    = trim($dcsValue[0]).";;".trim($dcsValue[0])."/".trim($dcsValue[1]).";;".trim($dcsValue[0])."/".trim($dcsValue[1])."/".trim($dcsValue[2]);
        }elseif(isset($dcsValue[1])){
            $dcs_cat_path[$vendor_cat_path]    = trim($dcsValue[0]).";;".trim($dcsValue[0])."/".trim($dcsValue[1]);
        }else{
            $dcs_cat_path[$vendor_cat_path]    = trim($dcsValue[0]);
        }*/

        $vendor[] = $value[0];
        $vendor[] = $value[1];
        $vendorValue = replaceChar($vendor);

        $dcs[] = $value[3];
        $dcs[] = $value[4];
        if(isset($value[5]))
            $dcs[] = $value[5];
        $dcsValue = replaceChar($dcs);

        $vendor_cat_path = trim($vendorValue[0])."/".trim($vendorValue[1]);
        if (isset($dcsValue[2]) && $dcsValue[2] != "") {
            $dcs_cat_path = trim($dcsValue[0]).";;".trim($dcsValue[0])."/".trim($dcsValue[1]).";;".trim($dcsValue[0])."/".trim($dcsValue[1])."/".trim($dcsValue[2]);
        }elseif(isset($dcsValue[1])){
            $dcs_cat_path = trim($dcsValue[0]).";;".trim($dcsValue[0])."/".trim($dcsValue[1]);
        }else{
            $dcs_cat_path = trim($dcsValue[0]);
        }
        unset($vendor, $vendorValue, $dcs, $dcsValue);

        /*$sql= "INSERT IGNORE INTO " . $tableName . " (vendor_name, vendor_cat_path, dcs_cat_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?)";
        $writeConn->query($sql, array($vendorCode, $vendor_cat_path, $dcs_cat_path, date( Varien_Date::DATETIME_PHP_FORMAT ), date( Varien_Date::DATETIME_PHP_FORMAT )));*/

    }

    function replaceChar($value){
        foreach ($value as $key => $val) {
            $newVal[$key] = implode("-", array_map('trim', explode("/", $val)));
        }
        return $newVal;
    }
    /*echo count($dcs_cat_path);
    print_r($dcs_cat_path);
    die();*/

    $sql = "SELECT vendor_cat_path, dcs_cat_path FROM " . $tableName . " WHERE vendor_name = '".$vendorCode."'";
    $data = $readConn->fetchPairs($sql, array('vendor_cat_path', 'dcs_cat_path'));

    echo count($data)."<br />";
    print_r($data);

