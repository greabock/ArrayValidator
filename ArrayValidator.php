<?php

class ArrayValidator{

    public $lasterror;
    public $errorcode;
    public $circuiterror;
    
    public function __call($name, $args)
    {
        $args[] = $this;
        return call_user_func_array([ $this, $name . '_' ], $args);
    }
    
    public static function __callStatic($name, $args)
    {
        return forward_static_call_array([ __CLASS__, $name . '_' ], $args);
    }
    
    private static function arraySameKeys_(Array $array1, Array $array2, $that = null)
    {
        if (array_merge(array_diff_key($array1, $array2), array_diff_key($array2, $array1)))
        {
            return self::setError('#! Не совпали ключи массивов.', ['element' => $array1, 'prototype' => $array2], 10);
        }
        return true;
    }
    
    private static function validateValue_($value, $rule, $circuit, $that = null)
    {   
        
        if (!empty($that) && !empty($that->callback)) {
            if (!call_user_func($that->callback, $value, $rule))
            {
                return self::setError('#! Значение {' . $value . '} не прошло валидацию по правилу {'.$rule.'}. (Коллбэк)', $circuit, 21, $that);
            }
            return true;
        }
        else
        {
            if (!preg_match($rule, (string)$value)){
                return self::setError('#! Значение {' . $value . '} не прошло валидацию по правилу {'.$rule.'}.', $circuit, 20, $that);
            }
            return true;
        }
    }
    
    private static function setCallback_(callable $callback, $that = null)
    {
        $that->callback = $callback;
        return $that;
    }
    
    private static function listValidate_($candidate, $prototype = null, $length = 0, $that = null)
    {
        if ($length && $length != count($candidate))
        {
            return self::setError('#! Длина массива не соответствует заявленной. Получено: ' . count($candidate). '. Должно быть: '. $length .'.', $candidate, 30, $that);
        }
        if (is_array($prototype))
        {
            foreach ($candidate as $key => $value)
            {
                if(!self::protoValidate($value, $prototype, $that))
                {
                    return false;
                }
            }
        }
        elseif (is_string($prototype))
        {
            foreach ($candidate as $key => $value) 
            {
                if (is_string($value))
                {
                    if (!self::validateValue($value, $prototype, $candidate, $that))
                    {
                       return false;
                    }                    
                }
            }
        }
        elseif ($prototype === null)
        {
            return true;
        }
        else
        {
            return self::setError('#! Не верный тип данных в прототипе. Допустимые значения: (string), (array), (null)' , $prototype, 40, $that); 
        }
        return true;
    }
    
    private static function protoValidate_($element, $prototype, $that = null)
    {
        if (array_key_exists('_prototype_', $prototype) || array_key_exists('_length_', $prototype))
        {
            $_candidate_ = $element;
            $_prototype_ = array_key_exists('_prototype_', $prototype) ? $prototype['_prototype_'] : null;
            $_length_    = array_key_exists('_length_', $prototype) ? $prototype['_length_'] :  0;
            if (!self::listValidate($_candidate_, $_prototype_, $_length_, $that))
            {
                return false;
            }
        }
        else
        {
            if (!self::arrayValidate($element, $prototype, $that)){
                return false;
            }
        }
        return true;
    }
    
    private static  function arrayValidate_($value, $prototype, $that = null)
    {
        if (is_array($value))
        {
            if (!self::arraySameKeys($value, $prototype, $that))
            {
                return false;
            }
            foreach ($value as $index => $element)
            {
                if (is_string($prototype[$index]) && (is_string($element)||is_null($element)||is_numeric($element)||is_bool($element)))
                {
                    if (!self::validateValue($element, $prototype[$index], $value, $that))
                    {
                        return false;
                    }
                }
                elseif (is_array($element) && is_array($prototype[$index]))
                {
                    if (!self::protoValidate($element, $prototype[$index], $that))
                    {
                        return false;
                    }
                }
                else
                {
                    return  self::setError('#! Не совпадают типы данных', ['element'=>$element,'prototype'=>$prototype[$index]], 41, $that);
                }
            }
        }
        else
        {
            return self::setError('#! Не верный тип данных элемента. Ожидался: Array. Получен: '. gettype($value) , $value, 42, $that);
        }
        return true;  
    }
    
    private static function getLastError_($that = null)
    {
        if (!empty($that))
        {
            return $that->lasterror;
        }
        else
        {
            return NULL;
        }
    }

    private static function getErrorCode_($that = null)
    {
        if (!empty($that))
        {
            return $that->errorcode;
        }
        else
        {
            return NULL;
        }
    }
    
    private static function getCircuitError_($that = null)
    {
        if (!empty($that))
        {
            return $that->circuiterror;
        }
        else
        {
            return NULL;
        }
    }
    private static function setError_($string, $circuit = null, $errorcode = null, $that = null)
    {
        if (!empty($that))
        {
            $that->lasterror = $string;
            $that->circuiterror = $circuit; 
            $that->errorcode = $errorcode;
        }
        return false;
    }
}