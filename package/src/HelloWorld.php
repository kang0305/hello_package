<?php

namespace Kang\HelloPackage;

class HelloWorld
{
    public function sayHello(): string
    {
        return "Hello, World!";
    }

    public function sayHelloTo(string $name): string
    {
        $this->sayHello();
        return "Hello, {$name}!";
    }
}
