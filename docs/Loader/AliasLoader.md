# `Soil\Loader\AliasLoader`

AliasLoader的目的是对`类的别名`进行统一管理。尤其在使用静态化（Statica）的时候，特别有用。

## 用法

```php
require('path/to/Loader/AliasLoader.php');

$loader = new \Soil\Loader\AliasLoader;
$loader->addAlias('Foo', 'FooReal');   // addALias('别名', '实际想要的类名');
$loader->addAlias('Bar', 'BarReal');
$loader->register();    // 注册好了后，下面就可以随便用Demo和Foo类了

Foo::hi();            // 实际执行的是 FooReal::hi()
Foo::say('hello.');   // 实际执行的是 FooReal::say('hello.')
Bar::do()             // 实际执行的是 BarReal::do()
```

## 功能

* 简单点说，就是对PHP内置的`class_alias()`进行包装。

## 详解

```php
<?php
Demo::hi();  // 肯定执行失败，因为还没有定义Demo类呢
```

* php执行到这里，发现不认识这个Demo类。
* php会启动 `__autoload` 机制，尝试从已经注册的__autoload策略中解析它。
* 要是能找到，就按照找到的策略解析出Demo；要是再找不到就真的抛出个运行时异常出来了，告诉用户找不到这个类。

此时，我们要想办法告诉__autoload，`Demo`类也就是`Foo\Bar\DemoKow`类。那么，php就知道了，`Demo::hi()`其实就是想执行`Foo\Bar\DemoKow::hi()`。

这种情况下，`Demo`就被称为`Foo\Bar\DemoKow`的**别名（alias）**。

AliasLoader就是用这个方法来登记别名，并统一管理大量别名，这样，项目中用起来就很爽了，飙代码时，哗哗的。

## 代码

> Talk is cheap, show me the code.  --Linus Torvalds

test.php

```php
require('path/to/Loader/AliasLoader.php');

$loader = new \Soil\Loader\AliasLoader;
$loader->addAlias('Demo', 'DemoFoo');   // addALias('别名', '实际想要的类名');
$loader->register();    // 下面就可以用Demo了

require('DemoFoo.php'); // 用前要确保DemoFoo类是存在的，实际项目中会用autoload来处理

Demo::say('hello.');    // 实际执行的是 DemoFoo::say('hello.')

echo PHP_EOL;
print_r(get_declared_classes());    // 看看现在所有已经定义的类列表
```

DemoFoo.php

```php
<?php
class DemoFoo
{
    public static function __callStatic($method, $args)
    {
        var_dump($method, $args);  // 显示传入的参数值
    }
}
```

显示结果是：

```php
string(3) "say"
array(1) {
  [0]=>
  string(2) "hi"
}
```
