<?php
require_once 'vendor/autoload.php';
use constructor\Constructor;

class TestClass
{
    private $test;

    public function __construct(string $str1 = null, string $str2 = null)
    {
        new Constructor($this, func_get_args());
    }

    public function construct0(): void
    {
        $this->construct1('first');
    }

    public function construct1(string $str1)
    {
        $this->construct2($str1, 'second world');
    }

    public function construct2(string $str1, string $str2)
    {
        $this->test = 123;
        echo $str1 .' '. $str2.'<br><br>';
    }

    public function test_value(){
        return $this->test;
    }
}

$class = new TestClass();
$class = new TestClass('start');
$class = new TestClass('simple', 'world');