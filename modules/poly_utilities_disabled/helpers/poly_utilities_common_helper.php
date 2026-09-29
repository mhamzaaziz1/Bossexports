<?php

defined('BASEPATH') or exit('No direct script access allowed');

class poly_utilities_common_helper
{
    public static $rels=[ 'follow', 'nofollow','alternate', 'author', 'bookmark', 'external', 'help', 'license', 'next', 'noreferrer', 'noopener','prev','search','tag'];
    public static $targets=[ '_self', '_blank', '_parent','_top'];
    public static $alphabet = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    public static $numbers = ['1','2','3','4','5','6','7','8','9'];
    public static $aio_supports_type = ['link','email','mobile','facebook_messenger','viber','whatsapp','other'];
    
    /**
     * Don't cache some resource files for support
     */
    public static function getParamDoNotCacheFile($paramCharacter = '')
    {
        return ((!empty($paramCharacter)) ? $paramCharacter : '?') . "date=" . time();
    }
    
    public static function getObjectByField($arr, $field, $content)
    {
        if (count($arr) == 0) return false;
        if (is_array($arr)) {
            foreach ($arr as $itm) {
                if (isset($content) && $itm[$field] === $content) {
                    return $itm;
                    break;
                }
            }
        }
        return null;
    }

    public static function isExisted($arr, $field, $content)
    {
        if (count($arr) == 0) return false;
        if (is_array($arr)) {
            foreach ($arr as $itm) {
                if (isset($content) && $itm[$field] === $content) {
                    return true;
                    break;
                }
            }
        }
        return false;
    }

    public static function removeDataByField($arr, $field, $value)
    {
        foreach ($arr as $key => $item) {
            if ($item[$field] === $value) {
                unset($arr[$key]);
                break;
            }
        }
        return array_values($arr);
    }

    public static function updateDataByField($arr, $field, $value, $obj)
    {
        foreach ($arr as $key => $item) {
            if ($item[$field] === $value) {
                unset($arr[$key]);
                $arr[]=$obj;
                break;
            }
        }
        return array_values($arr);
    }

    public static function generateUniqueID()
    {
        $uniqueID = uniqid();
        $hashedID = md5($uniqueID);
        return $hashedID;
    }

    public static function convertToFileName($string)
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9]+/', '-', $string);
        $string = trim($string, '-');
        $string = str_replace(' ', '-', $string);
        return $string;
    }

    public static function readFile($file_name, $directory)
    {
        if (file_exists($directory . '/' . $file_name)) {
            $content = file_get_contents($directory . '/' . $file_name);
            if ($content !== false) {
                return $content;
            }
        }
        return '';
    }
    public static function saveToFile($file_name, $directory, $content, $is_overwrite = false)
    {
        $file_path = $directory . '/' . $file_name;

        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            return 0;
        }

        if ($is_overwrite && file_exists($file_path) && !unlink($file_path)) {
            return 0;
        }

        if (!$is_overwrite && file_exists($file_path)) {
            return 0;
        }

        return file_put_contents($file_path, $content) !== false ? 1 : 0;
    }

    public static function render_select($id, $options, $value, $label='', $group_class='', $select_class=''){
        
        $rest='<div class="'.$group_class.'">'.((!empty($label))?"<label>{$label}</label>":'');
        $rest.="<select class='form-control ".$select_class."' id='{$id}'>";
        foreach ($options as $key => $option) {
            $selected=($option===$value)?' selected':'';
            $rest.="<option value='{$option}'{$selected}>{$option}</option>";
        }
        $rest=$rest."</select></div>";
        return $rest;
    }
}
