<?php
namespace constructor;


class Constructor
{
    public function __construct($class, $args)
    {
        $i = count($args);
        if (method_exists($class, $f = 'construct' . $i) and $i !== 0) {
            call_user_func_array(array($class, $f), $args);
        } elseif (method_exists($class, $f = 'construct' . $i) and $i === 0) {
            call_user_func_array(array($class, $f), $args);
        } else {//запускает конструктор на уровень выше.
            $i++;
            if (method_exists($class, $f = 'construct' . $i)) {
                call_user_func_array(array($class, $f), $args);
            }

        }
    }
}