<?php

class ArrayValidator{

    private $lasterror;
    private $circuiterror;
    
    public function __call($name, $args)
    {
        $name = $name . '_' ;
        return call_user_func_array([self ,$name], $args);
    }
    
    public static function __callStatic($name, $args)
    {
        $name = $name . '_' ;
        return call_user_func_array([self,$name], $args);
    }
    
    private function arraySameKeys_(Array $array1, Array $array2)
    {
        if (array_merge(array_diff_key($array1, $array2), array_diff_key($array2, $array1)))
        {
            return self::setError('#! Не совпали ключи массивов.', ['element' => $array1, 'prototype' => $array2]);
        }
        return true;
    }
    
    private function validateValue_($value, $rule, $circuit)
    {   
        
        if (!empty($this) && !empty($this->callback)) {
            if (!call_user_func($this->callback, $value, $rule))
            {
                return self::setError('#! Значение {' . $value . '} не прошло валидацию по правилу {'.$rule.'}. (Коллбэк)', $circuit);
            }
            return true;
        }
        else
        {
            if (!preg_match($rule, (string)$value)){
                return self::setError('#! Значение {' . $value . '} не прошло валидацию по правилу {'.$rule.'}.', $circuit);
            }
            return true;
        }
    }
    
    private function setCallback_(callable $callback)
    {
        $this->callback = $callback;
        return $this;
    }
    
    private function listValidate_($candidate, $prototype, $length = 0)
    {
        if ($length && $length != count($candidate))
        {
            return self::setError('#! Длина массива не соответствует заявленной. Получено: ' . count($candidate). '. Должно быть: '. $length .'.', $candidate);
        }
        if (is_array($prototype))
        {
            foreach ($candidate as $key => $value)
            {
                if(!self::protoValidate($value, $prototype))
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
                    if (!self::validateValue($value, $prototype, $candidate))
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
            return self::setError('#! Не верный тип данных в прототипе.', $prototype); 
        }
        return true;
    }
    
    private function protoValidate_($element, $prototype)
    {
        if (array_key_exists('_prototype_', $prototype) || array_key_exists('_length_', $prototype))
        {
            $_candidate_ = $element;
            $_prototype_ = array_key_exists('_prototype_', $prototype) ? $prototype['_prototype_'] : null;
            $_length_    = array_key_exists('_length_', $prototype) ? $prototype['_length_'] :  0;
            if (!self::listValidate($_candidate_, $_prototype_, $_length_))
            {
                return false;
            }
        }
        else
        {
            if (!self::arrayValidate($element, $prototype)){
                return false;
            }
        }
        return true;
    }
    
    private  function arrayValidate_($value, $prototype)
    {
        if (is_array($value))
        {
            if (!self::arraySameKeys($value, $prototype))
            {
                return false;
            }
            foreach ($value as $index => $element)
            {
                if (is_string($prototype[$index]) && (is_string($element)||is_null($element)||is_numeric($element)||is_bool($element)))
                {
                    if (!self::validateValue($element, $prototype[$index], $value))
                    {
                        return false;
                    }
                }
                elseif (is_array($element) && is_array($prototype[$index]))
                {
                    if (!self::protoValidate($element, $prototype[$index]))
                    {
                        return false;
                    }
                }
                else
                {
                    return  self::setError('#! Не совпадают типы данных', ['element'=>$element,'prototype'=>$prototype[$index]]);
                }
            }
        }
        else
        {
            return self::setError('#! Не верный тип данных элемента. Ожидался: массив. Получен: немассив :-)', $value);
        }
        return true;  
    }
    
    private function getLastError_()
    {
        if (!empty($this))
        {
            return $this->lasterror;
        }
        else
        {
            return NULL;
        }
    }
    
    private function getCircuitError_()
    {
        if (!empty($this))
        {
            return $this->circuiterror;
        }
        else
        {
            return NULL;
        }
    }
    private function setError_($string, $circuit = null)
    {
        if (!empty($this))
        {
            $this->lasterror = $string;
            $this->circuiterror = $circuit; 
        }
        return false;
    }
}