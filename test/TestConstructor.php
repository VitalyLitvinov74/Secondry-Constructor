<?php

use constructor\Constructor;

require_once '../vendor/autoload.php';
require_once 'ForTestClass.php';

class TestConstructor
{
    private $constructor;
    private $errors;

    public function __construct($test = null, $test2 = null, $test3 = null)
    {
        $this->constructor = new Constructor($this);
    }

    private function build_3(string $test, string $test2, string $test3)
    {
        $i = 1;
        echo '<br> Вызван build_3';
    }

    private function build_1(ForTestClass $test){
        $i = 1;
        echo '<br> Вызван build_1';
    }

    private function build_1_string(string $test){
        echo '<br> Вызван build_1_string';
    }

    public function startTest()
    {
        if ($this->value_type()
            and $this->type_equaled()
            and $this->is_main_construct()
            and $this->name_of_executable_constructor()
        ) {
            return true;
        }
        return false;
    }

    private function set_public_method($nameMethod, ...$args)
    {
        $ref = new ReflectionClass(get_class($this->constructor));
        $method = $ref->getMethod($nameMethod);
        $method->setAccessible(true);
        switch ($nameMethod) {
            default:
                $ret = $method->invokeArgs($this->constructor, [$args]);
                break;
            case "type_equaled" or 'is_main_construct' or 'name_of_executable_constructor':
                $ret = $method->invokeArgs($this->constructor, $args);
                break;
        }
        $method->setAccessible(false);
        return $ret;
    }

    private function value_type(): bool
    {
        $double = $this->set_public_method('value_type', 123.2);
        if ($double === 'double') {
            return true;
        }
        $this->errors[] = 'value_type';
        return false;
    }

    private function type_equaled(): bool
    {
        if ($this->set_public_method('type_equaled', 'double', 1232.22)
            and !$this->set_public_method('type_equaled', 'integer', 1232.22)
            and $this->set_public_method('type_equaled', 'array', ['asd'])
            and !$this->set_public_method('type_equaled', 'doublee', 1232.22)
        ) {
            return true;
        }
        $this->errors[] = 'type_equaled';
        return false;
    }

    private function is_main_construct(): bool
    {
        if (!$this->set_public_method('is_main_construct', [123, 'test2', 'test3'])
            and !$this->set_public_method('is_main_construct', [['test'], 'test2', 'test3'])
            and $this->set_public_method('is_main_construct', ['test', 'test2', 'test3'])
        ) {
            return true;
        }
        $this->errors[] = 'is_main_construct';
        return false;
    }

    private function name_of_executable_constructor(): bool
    {
        $name1 = $this->set_public_method('name_of_executable_constructor', [123, 'test2', 'test3']);
        $name2 = $this->set_public_method('name_of_executable_constructor', ['123', 'test2', 'test3']);
        if ($name1 === 'build_3_int_string_string' and $name2 === 'build_3') {
            return true;
        }
        $this->errors[] = 'name_of_executable_constructor';
        return false;
    }

    public function result()
    {
        if($this->startTest()){
            echo  '<br>'.'Все хорошо'.'<br>';
        }else{
            echo '<pre>';
            var_dump($this->errors);
            echo '</pre>';
        }
    }


}

$test = new TestConstructor("test", "test2", 'test3'); //вызовет build_3
$test->result();
$test = new TestConstructor(new ForTestClass()); //Вызовет build_1
$test->result();
$test = new TestConstructor("hello world"); //вызовет build_1_string
$test->result();
