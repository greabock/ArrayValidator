ArrayValidator
==============
##Описание

###Данный класс позволяет:
 - проверять сложноструктурированые массивы на соответствие прототипам
 - проверять значения *конечных* элементов на соответствие регулярным выражениям
 - задавать собственные правила валидации и обрабатывать их в callback-функции
 - получать отчет об ошибках (доступно только в контексте объекта валидатора)

###Где это может может быть полезным?
 Например валидация нестатичного-структурированного JSON-запроса (не путать с ответом).
 Если честно, ничего больше в голову не приходит.

###Использование

####Простое сравнение массивов.
статичный контекст
```php
  $array = array('name' => 'vasya', 'age' => '22');
  $proto = array('name' => '/.*/', 'age'=> '/[0-9]+/');
  var_dump(ArrayValidator::arrayValidate($array, $proto));
  //bool(true)
```

объектный контекст(позволяет получить отчет об ошибке)
```php
  $v = new ArrayValidator;
  $array = array('name' => 'vasya', 'age' => '22');
  $proto = array('name' => '/.*/', 'age'=> '/[A-Z]+/');
  
  echo '<pre>';
  var_dump($v->arrayValidate($array, $proto));
  var_dump($v->getLastError());
  var_dump($v->getCircuitError());
  echo '</pre>';
 
  // bool(false)
  // string(93) "#! Значение {22} не прошло валидацию по правилу {/[A-Z]+/}."
  // array(2) {
  //   ["name"]=>
  //   string(5) "vasya"
  //   ["age"]=>
  //   string(2) "22"
  // }
```

список прототипов, и его длина
```php
$array = array(
        array('name' => 'vasya', 'age' => '22'),
        array('name' => 'vasya', 'age' => '22'),
        array('name' => 'vasya', 'age' => '22'),
        array('name' => 'vasya', 'age' => '22'),
        array('name' => 'vasya', 'age' => '22'),
);
$proto = array('name' => '/.*/', 'age'=> '/[0-9]+/');

echo '<pre>';
var_dump(ArrayValidator::listValidate($array, $proto));
var_dump(ArrayValidator::listValidate($array, $proto, 3));
var_dump(ArrayValidator::listValidate($array, $proto, 5));
echo '</pre>';
// bool(true)
// bool(false)
// bool(true)
```
