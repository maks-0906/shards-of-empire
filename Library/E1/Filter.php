<?php

/**
 * Generic Filter class
 */
class Filter {

    static private $fieldName;

    /**
     * Return typed $param or $default
     * @param mixed $param
     * @param mixed $default
     * @return mixed 
     */
    static function Clear($param, $default) {
        $bIsConvert = settype($param, gettype($default));
        return $bIsConvert ? $param : $default;
    }

    static function ClearTags($param, $allowable_tags = null) {
        return strip_tags($param, $allowable_tags);
    }

    static function ClearPHPTags($param) {
        return str_replace(array('<?php', '?>'), array('&lt?php', '?&gt'), $param);
    }

    static function ClearEmail($param) {
        return filter_var($param, FILTER_VALIDATE_EMAIL);
    }

    static function ClearData($sData, $format = "Y-m-d") {
        $oData = new DateTime($sData);
        return $oData->format($format);
    }

    static function ClearUrl($url, $default = false, $scheme='http') {
        if ($scheme != parse_url($url, PHP_URL_SCHEME)) {
            return $default;
        }
        return filter_var($url, FILTER_VALIDATE_URL, array('default' => $default
                        //, 'flags' =>  FILTER_FLAG_PATH_REQUIRED
                        )
        );
    }

    static private function GetField($Item) {
        if (!array_key_exists(Filter::$fieldName, $Item)) {
            return null;
        }
        return $Item[Filter::$fieldName];
    }

    static function GetArrayValue(array $Rows, $sFieldName) {
        //TODO: in 5.3 array_map(function ($v) {return $v[$field];},$Rows)
        //PHP 5.2 Suxx
        self::$fieldName = $sFieldName;
        $aItem = array_map('Filter::GetField', $Rows);
        self::$fieldName = '';
        return $aItem;
    }

}