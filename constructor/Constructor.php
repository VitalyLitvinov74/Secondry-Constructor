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
    private const construct_name = "build_";

    public function __construct($class, $args)
    {
        try {
            $this->class = $class;
            $this->reflection_class = new \ReflectionClass(get_class($class));
        } catch (\ReflectionException $e) {
            $this->err($e);
        }
        $this->run($args);
    }

    /**
     * Поиск и вызов необходимого конструктора
     * Критерии выбора:
     * 1. типы аргументов должны совпадать
     * 2. Кол-во аргументов должно совпадать
     * Если такого конструктора нет то ошибка.
     *
     * @param array $args - входные параметры класса
     * */
    private function run(array $args): void
    {
        $this->run_1($args);
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
     */
    private function calling_method_name(array $args): string
    {
        $count = count($args);
        $suffix = '';
        if (!$this->is_main_construct($args)) {
            foreach ($args as $arg) {
                $str = $this->attribute_type($arg);
                if ($str == 'integer') {
                    $str = 'int';
                }
                $suffix .= '_' . $str;
            }
        }
        if ($suffix !== '') {
            return self::construct_name . $count . $suffix;
        }
        return self::construct_name . $count;
    }

    /**
     * Определяет какой вторичный конструктор должен вызываться
     * с суффиксом или без суффикса.
     * @throws ReflectionException
     */
    private function is_main_construct(array $args): bool
    {
        $count = count($args);
        $name = self::construct_name . $count;
        if (method_exists($this->class, $name)) {
            $ref = new \ReflectionMethod($this->class, $name);
            $params = $ref->getParameters();
            for ($i = 0; $i <= count($params) - 1; $i++) {
                /**@var ReflectionParameter $param [$i] */
                $type = $params[$i]->getType()->__toString(); //не проверялась работа с именами классов.
                if ($type !== $this->attribute_type($args[$i])) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Вызывает приватный конструктор, и передает в него аргументы
     *
     * @param string $const_suffix
     * @param array  $args
     *
     * @throws \ReflectionException
     */
    private function call_private_constructor(array $args): void
    {
        $method = $this->reflection_class->getMethod($this->calling_method_name($args));
        $method->setAccessible(true);
        $method->invokeArgs($this->class, $args);
        $method->setAccessible(false);
    }

    /**
     * запускает конструктор в который передано от 0 до всех параметров.
     *
     * @param array $args - входные параметры класса
     */
    private function run_1(array $args): void
    {
        try {
            $this->call_private_constructor($args);
        } catch (\ReflectionException $e) {
            $this->err($e);
        }
    }

    private function err(ReflectionException $e)
    {
        echo "<pre>";
        echo $e->getMessage();
        echo "</pre>";
        die;
    }
}