<?php

class MyClass
{
    public function only(string $key): mixed
    {
        return $key;
    }
}

$obj = new MyClass();
$result = $obj->only('email');
