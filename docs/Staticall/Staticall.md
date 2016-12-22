# Soil\Staticall

Staticall实现了在程序中进行无声明的直接静态调用的功能，类似于Laravel的Facade，但是更加清晰简洁。

## 用法

```php
<?php
require __DIR__ . '/../autoload.php';   // composer的机制

use Soil\Staticall;

/* 随便定义的一个测试类 */
class Demo
{
    public function say($a, $b='aa')
    {
        return PHP_EOL . $a . ' and ' . $b;
    }
}

$app = new Soil\Container;           // 新容器
$app->bind('demo','Demo');           // $app['demo'] = 'Demo';

Staticall::boot($app);               // 启动Staticall机制
Staticall::set('Foo', 'demo');       // 告诉PHP，Foo类关联的是$app['demo']
Staticall::set('Bar', 'demo');       // 告诉PHP，Bar类也关联的是$app['demo']

echo Foo::say('hi');                 // 显示 hi and default。
echo Bar::say('foo', '222');         // 显示 foo and 222
```

酷吧，其实根本没有Foo或者Bar这两个被调用的类！实际调用的Demo也没有静态方法say！而且，连Laravel中要定义的DemoFacade类都可以省去！:)

## 什么是Staticall

首先，我承认，网上其实根本没有`Staticall`这个词，`Staticall`这个词是我杜撰出来的，表示`Static`+`Call`。虽然俗话说，取名是最困难的事情之一，但是对于文学和英文功底比较深的Macc哥来说，胡诌个靠谱的名词出来都是小事。:)

`Staticall`主要目的是实现无声明静态调用，就如上面的`Foo::say()`和`Bar::say()`那样，只要你能事先启动Staticall机制，并且告诉系统`Foo`就是指`$app['demo']`，剩下的就是放心大胆的用就行。用的时候，Staticall会帮你去找到`$app['demo']`，执行调用，然后给你想要的结果。

--------
参见 <https://github.com/maccliu/soil>