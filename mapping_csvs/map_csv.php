<?php

    // Starting of execution of script

    require_once '../app/Mage.php';
    //require_once 'includes/Pushkarcreations_Petstore.php';

    Mage::setIsDeveloperMode(true);
    umask(0);
    Mage::app('admin');
    Mage::register('isSecureArea', 1);
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

    set_time_limit(0);
    ini_set('display_errors', 1);
    ini_set('memory_limit','1024M');
    //echo "<pre>";

    $name = 'Pushkarcreations';

    $str = Mage::app()->getRequest()->getParam('vendor');
    $delimiter = Mage::app()->getRequest()->getParam('delimiter');
    if($str){
        $class = $name.'_'.ucfirst($str);
        $vendor = new $class();
    }else{
        echo "<p>Error : Please Enter Vendor Name in URL. i.e. \"?vendor=petstore\"</p>";
        exit;
    }

    $stdCsv = new Varien_File_Csv();
    $csv = new Varien_File_Csv();
    if($delimiter)
        $csv->setDelimiter($delimiter);

    $default = $stdCsv->getData('masterupload.csv'); // Get standard csv data.
    $stdFields = array_shift($default);

    $tgtData = $csv->getData($str."_input.csv"); // Get vedor csv data.
    $tgtFields= array_shift($tgtData);

    // Make default data array
    $defData = $vendor->getDefaultData($stdFields);

    // Replace keys array of standard magento fields
    $keyReplace = $vendor->replaceKeysWithStandard();

    $csvData = array();
    foreach ($tgtData as $data) {
        $data = array_combine($tgtFields, $data);
        $data = $vendor->formatFieldsPerMapping($data);
        $skipArr = $vendor->skipProductsFromCategories();
        foreach ($data as $key => $value){
            if( array_key_exists($key, $keyReplace) ){
                if( $keyReplace[$key] == 'categories' && !empty($skipArr) ){
                    if( in_array($value, $skipArr) ){
                        continue 2;
                        //print_r($value);die();
                    }
                }
                $csvData[$keyReplace[$key]] = is_numeric($value) ? $value : implode("<br>", array_map('trim', array_filter(explode("<br> ", $value))));

                /*
                 * Remove other then standard fields from vendor csv -
                 * Deteting using same key/value pair in replacing array
                 */
                if( $keyReplace[$key] == $key )
                    unset($csvData[$keyReplace[$key]]);
            }elseif( $key != "" ){
                $csvData[$key] = $value;
            }
        }
        $output[] = $vendor->mage_parse_args( $csvData, $defData );
    }

    //print_r($output);
    //exit();

    // Adding headers to columns of csv.
    array_unshift($output, $stdFields);

    // Output final standard magento csv
    if( $stdCsv->saveData( $str.'_output.csv', $output ) ){
        echo "<p>Standard Magneto csv has been generated from <i>".ucfirst($str)."</i> vendor.</p>";
    }


?>
