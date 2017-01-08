# Soil\Loader

## 用法

```php
// 第一步，引入Loader.php
require '/path/to/soil/Loader.php';

// 第二步，new一个新对象
$loader = new Soil\Loader;

// 第三步，添加一系列映射关系，可链式调用
$loader->addNamespace('Foo\\Bar', __DIR__ . '/test1')
       ->addAlias('B', 'Foo\Bar\Kow')
       ->addNamespace('App', __DIR__ . '/test2');

// 第四步，注册到spl_autoload
$loader->register();

/* 上面的第三步和第四步顺序可以随意，只要在实际使用前完成这两步就行了 */
...

/* 完成上面的定义后，下面就可以随意使用了 */
$a = new Foo\Bar\Kow;     // 会自己去找 ./test1/Kow.php 是否存在，且文件中是否定义了 Foo\Bar\Kow
$b = new B;               // 即 $b = new Foo\Bar\Kow
$c = new App\Kow;         // 会自己去找 ./test2/Kow.php 是否存在，且文件中是否定义了 App\Kow
```
