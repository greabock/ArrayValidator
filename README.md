ArrayValidator
==============
##Описание

###Данный класс позволяет:
 - проверять сложноструктурированые массивы на соответствие прототипам
 - проверять значения *конечных* элементов на соответствие регулярным выражениям
 - задавать собственные правила валидации и обрабатывать их в callback-функции
 - получать отчет об ошибках (доступно только в контексте объекта валидатора)

###Где это может может быть полезным?
 Например валидация нестатичного сложно-структурированного JSON-запроса.
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
> Все дальнейшие примеры будут приведены в статичном контексте (за исключением использования callback для валидации), но точно также могут быть использованы и в объектном контексте.

####список прототипов, и его длина
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

####простой вложенный массив
```php
  $array = array(
    'name' => 'vasya', 
    'age' => '22', 
    'phones'=> array(
        'home'=>'255533',
        'mobile'=>'35775533',
        ),
  );
  $proto = array(
    'name' => '/.*/',
    'age'=> '/[0-9]+/',
    'phones'=> array(
        'home'=>'/[0-9]+/',
        'mobile'=>'/[0-9]+/',
    ),
  );
  var_dump(ArrayValidator::arrayValidate($array, $proto));
  // bool(true)
```

####Вложенный массив со списком простых прототипов. 
> Примечание: Значения `_length_` и/или `_prototype_` могут быть опущены и поведение будет вполне предсказуемым.
```php
  $array = array(
    'name' => 'vasya', 
    'age' => '22', 
    'phones'=> array(
        '25553343',
        '35778833',
        '35776533',
        '35733633',
        '35775533',
        ),
  );
  $proto = array(
    'name' => '/.*/',
    'age'=> '/[0-9]+/',
    'phones'=> array(
        '_length_' => '5',
        '_prototype_' => '/[0-9]+/',
    ),
  );
  var_dump(ArrayValidator::arrayValidate($array, $proto));
  // bool(true)
```

