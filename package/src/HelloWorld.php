<?php

namespace Kang\HelloPackage;

class HelloWorld
{
    /**
     * @deprecated v2.0.0 棄用
     */
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
