<?php

namespace constructor;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * Вторичные конструкторы ничего не возвращают они только конфигурируют класс.
 * @property \ReflectionClass $reflection_class
 * @property object           $class
 * @property array            $debug
 * @property string           $root_name
 * @property array            $args
 */
class Constructor
{

    private $reflection_class;
    private $class;
    private $debug;
    private $args;
    private $root_name;

    /**
     * @param object     $class - входной класс у которого необходимо запустить вторичные конструкторы.
     * @param array|null $args - аргументы которые передаются в класс при создании экзземпляра.
     * @param string     $construct_name - "корень" имени конструктора.
     */
    public function __construct($class, array $args = null, $construct_name = "build_")
    {
        $this->class = $class;
        try {
            $this->reflection_class = new ReflectionClass(get_class($class));
        } catch (ReflectionException $e) {
            $this->err($e);
        }
        $this->root_name = $construct_name;
        if (!$args) {
            $this->debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
            $args = $this->args();
        }
        $this->args = $args;
        $this->run($args);
    }

    /**
     * Поиск и вызов необходимого конструктора
     * Если нужного конструктора не будет то выбросит исключение.
     *
     * @param array $args - массив аргументов которые поступили в конструктор класса.
     */
    private function run(array $args): void
    {
        try {
            $method_name = $this->name_of_executable_constructor($args);
            if (method_exists($this->class, $method_name)) {
                $reflection_method = $this->reflection_class->getMethod(
                    $method_name
                );
                $reflection_method->setAccessible(true);
                $reflection_method->invokeArgs(
                    $this->class,
                    $args
                );
                $reflection_method->setAccessible(false);
            }
        } catch (\ReflectionException $e) {
            $this->err($e);
        }
    }

    /**
     * @param array $args - аргументы коотрые поступили в конструктор при осздании класса.
     *
     * @return string - имя конструктора, который необходимо выполнить.
     * @throws ReflectionException
     */
    private function name_of_executable_constructor(array $args): string
    {
        $suffix = '';
        if (!$this->is_main_construct($args)) {
            foreach ($args as $arg) {
                $str = $this->value_type($arg);
                if ($str == 'integer') {
                    $str = 'int';
                }
                $suffix .= '_' . $str;
            }
        }
        if ($suffix !== '') {
            return $this->root_name . count($args) . $suffix; //build_1_string
        }
        return $this->root_name . count($args); //build_1
    }

    /**
     * Определяет какой вторичный конструктор должен вызываться
     * с суффиксом или без суффикса.
     *
     * @param array $args
     *
     * @return bool
     * @throws ReflectionException
     */
    private function is_main_construct(array $args): bool
    {
        $name = $this->root_name . count($args); //build_3
        if (method_exists($this->class, $name)) {
            $ref = new \ReflectionMethod($this->class, $name);
            $params = $ref->getParameters(); //получаем параметры метода build_1
            for ($i = 0; $i <= count($params) - 1; $i++) {
                if (!$type_equaled = $this->type_equaled($params[$i]->getType()->getName(), $args[$i])) {
                    return $type_equaled;
                }
            }
            //Если все типы данных, переданных в главный конструктор
            //полностью сходятся с существующим build_1 то следовательно нужно вызвывать build_1
            //следовательно проверяемый конструктор - конструктор без суффикса.
            return true;
        }
        return false;
    }

    private function value_type($val): string
    {
        $type = gettype($val); //если тип простой.
        if ($type == 'object') {
            return get_class($val);
        }
        return $type;
    }

    private function type_equaled($refType, $constructorType): bool
    {
        if ($this->value_type($constructorType) === $refType) {
            return true;
        }
        return false;
    }

    private function args(): array
    {
        return $this->debug[1]['args'];
    }

    private function err(ReflectionException $e)
    {
        echo "<pre>";
        echo $e->getMessage();
        echo "</pre>";
    }
}