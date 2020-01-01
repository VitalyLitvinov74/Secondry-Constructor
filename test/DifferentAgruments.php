<?php

use constructor\Constructor;

require_once '../vendor/autoload.php';
/**
 * Вторичные конструкторы не возвращают текущий объект они его конфигурируют.
 * Возвращает текущий объект только метод __construct
*/
class DifferentAgruments
{

    private $val1;
    private $val2;

    public function __construct($type1 = null, $type2 = null) {
        new Constructor($this, func_get_args());
    }

    private function build_0(){
        $this->build_1("this");
    }

    private function build_1(string $val){
        $this->build_2($val, "- is string constructor");
    }

    /**
     * Вдруг мне захотелось в конструктор передавать не строку а число.
     * -----
     * В данном случае можно сценарий действий направить на:
     * конструктор build_2 (основной)
     * или на build_2_int
     * или на build_2_int_string - суффиксы пишутся по передаваемым типам.
    */
    private function build_1_int(int $val){
        $this->build_2((string) $val, ' - is int constructor');
    }

    private function build_2(string $val, string $val2){
        $this->val1 = $val;
        $this->val2 = $val2;
    }

    /**
     * Вдруг мне вместо двух строк захотелось передавать 2 числа.
    */
    private function build_2_int_int(int $val, int $val2){
        $this->build_2((string) $val, (string) $val2);
    }

    public function render(){
        return $this->val1 . ' '. $this->val2;
    }

}

$difArgs = new DifferentAgruments();
echo $difArgs->render() . '<br>'; //this is string constructor

$difArgs = new DifferentAgruments("str1"); //ttrwersd - is string constructor
echo $difArgs->render() . '<br>';

$difArgs = new DifferentAgruments(1); //1 - is int constructor
echo $difArgs->render() . '<br>';

$difArgs = new DifferentAgruments(222, 3333); //1 - is int constructor
echo $difArgs->render() . '<br>';

$difArgs = new DifferentAgruments("str1", "str2", 'str3'); //Method build_3 does not exist
echo $difArgs->render() . '<br>';