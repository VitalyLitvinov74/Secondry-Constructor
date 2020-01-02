<?php

namespace constructor;

use ReflectionException;
use ReflectionParameter;

/**
 * Вторичные конструкторы ничего не возвращают они только конфигурируют класс.
 * @property \ReflectionClass $reflection_class
 * @property object           $class
 */
class Constructor
{

    private $reflection_class;
    private $class;
    private $debug;
    private const construct_name = "build_";

    public function __construct()
    {
        try {
            $this->debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT,2);
            $this->reflection_class = new \ReflectionClass($this->className());
        } catch (\ReflectionException $e) {
            $this->err($e);
        }
        $this->run();
    }

    /**
     * Поиск и вызов необходимого конструктора
     *
     * Если нужного конструктора не будет то выбросит исключение.
     * */
    private function run(): void
    {
        try {
            $method = $this->reflection_class->getMethod(
                $this->generate_method_name()
            );
            $method->setAccessible(true);
            $method->invokeArgs(
                $this->classObject(),
                $this->args()
            );
            $method->setAccessible(false);
        } catch (\ReflectionException $e) {
            $this->err($e);
        }
    }

    private function attribute_type($val): string
    {
        $type = gettype($val); //если тип простой.
        if (/*$type == 'unknown type' or */ $type == 'object') {
            return get_class($val);
        }
        return $type;
    }

    /**
     * Здесь происходит генерация имени метода.
     * @throws ReflectionException
     */
    private function generate_method_name(): string
    {
        $suffix = '';
        if (!$this->is_main_construct()) {
            foreach ($this->args() as $arg) {
                $str = $this->attribute_type($arg);
                if ($str == 'integer') {
                    $str = 'int';
                }
                $suffix .= '_' . $str;
            }
        }
        if ($suffix !== '') {
            return self::construct_name . $this->count() . $suffix;
        }
        return self::construct_name . $this->count();
    }

    /**
     * Определяет какой вторичный конструктор должен вызываться
     * с суффиксом или без суффикса.
     * @throws ReflectionException
     */
    private function is_main_construct(): bool
    {
        $args = $this->args();
        $name = self::construct_name . $this->count();
        if (method_exists($this->className(), $name)) { //если существует метод на подобии build_1
            $ref = new \ReflectionMethod($this->className(), $name);
            $params = $ref->getParameters(); //получаем параметры метода build_1
            for ($i = 0; $i <= count($params) - 1; $i++) {
                /**@var ReflectionParameter $param [$i] */
                $type = $params[$i]->getType()->__toString(); //не проверялась работа с именами классов.
                //сверяются типы данных переданных на главный конструктор
                // и типы данных которые присутствуют в build_1
                // Если данные не совпадают значит нужно вызывать точно не build_1
                // а вторичный конструктор с суффиксом.
                if ($type !== $this->attribute_type($args[$i])) {
                    return false;
                }
            }
            //Если все типы данных, переданных в главный конструктор
            //полностью сходятся с существующим build_1 то следовательно нужно вызвывать build_1
            //следовательно проверяемый конструктор - конструктор без суффикса.
            return true;
        }
        return false;
    }

    private function className(): string
    {
        return $this->debug[1]['class'];
    }

    private function args(): array
    {
        return $this->debug[1]['args'];
    }

    private function classObject()
    {
        return $this->debug[1]['object'];
    }

    private $count;
    private function count(){
        if (!$this->count and $this->count !==0){
            $this->count = count($this->args());
        }
        return $this->count;
    }

    private function err(ReflectionException $e)
    {
        echo "<pre>";
        echo $e->getMessage();
        echo "</pre>";
        die;
    }
}