# \Soil\Container

Container可以存储两种类型的条目：一种是参数型（parameter），一种是对象型。

## 参数型条目

参数型条目主要用于保存配置信息：

```php
$container = new \Soil\Container;

// 用数组形式设置
$container['db.host']   = 'localhost';
$container['db.driver'] = 'mysql';

// 用对象形式设置
$container->set('db.host',   'localhost');
$container->set('db.driver', 'mysql');
```

对于参数型的条目，get时，返回设置的值：

```php
$a = $container['db.host'];     // $a='localhost'
$b = $container['db.driver'];   // $b='mysql'
```

## 对象型条目

对象型条目主要用于保存服务、对象实例等。

有两种把对象条目注册到容器的方法：`register()`和`registerShared()`。

```php
register($key, $class)              // 普通注册
registerShared($key, $class)        // 共享注册，或叫单例模式
```

其中，`$class`在注册时可以有三种类型：

* 以类名方式：`\Your\Class\Name`
* 以闭包方式：`function(..){...}`
* 以实例方式：已经new好的对象

### 1. 用`register()`方式注册的：

1. 对于以类名注册的，get时，返回此类名的一个 **新实例对象**。
2. 对于以闭包注册的，get时，执行此闭包函数，返回一个 **新的执行结果**。
3. 对于以实例注册的，get时，仍然返回此实例对象。

注册时：

```php
$container = new \Soil\Container;

// 以类名注册
$container->register('foo', '\\Your\\Class\\Name');

// 以闭包注册
$container->register('bar', function(...){...});

// 以实例注册
$instance = new SomeClass;
$container->register('kow', $instance);
```

get时：

```php
// 等效于 $foo = new \Your\Class\Name;
$foo = $container['foo'];         // 数组形式访问
$foo = $container->get('foo');    // 对象形式访问

// $ins1 = $instance
$ins1 = $container['kow'];        // 数组形式访问
$ins1 = $container->get('kow');   // 对象形式访问
```

### 2. 用`registerShared()`注册的：

1. 对于以类名注册的，get时，返回此类名的 **共享实例**。此共享实例会在首次get时生成。
2. 对于以闭包注册的，get时，返回此闭包的 **执行结果**。此执行结果会在首次调用时生成。
3. 对于以实例注册的，get时，仍然返回此实例对象。

注册时：

```php
$container = new \Soil\Container;

// 以类名注册共享
$container->registerShared('foo', '\\Your\\Class\\Name');

// 以闭包注册共享
$container->registerShared('bar', function(...){...});

// 以实例注册共享
$instance = new SomeClass;
$container->registerShared('kow', $instance);
```

get时：

```php
$container = new \Soil\Container;

$container->registerShared('foo', '\\Your\\Class\\Name');
$foo1 = $container['foo']; // 第一次时，$foo1 = new \Your\Class\Name;
$foo2 = $container['foo']; // 第二次时，$foo2 = $foo1
$foo3 = $container['foo']; // 第三次时，$foo3 = $foo1
```

## 代码示例

### 1. 传入类名的测试

```php
<?php
// 这个一个简单的测试类
class Foo {
    public $num;
    public function __construct(){
        $this->num = rand(1, 100);
    }
}

require(__DIR__ . '/../soil/src/Container.php');

$container = new Soil\Container;

// register()测试
$container->register('aaa', 'Foo');
$a1 = $container['aaa'];
echo PHP_EOL . $a1->num;    // 90   <--第一次get生成
$a2 = $container['aaa'];
echo PHP_EOL . $a2->num;    // 22   <--第二次get又生成了新的

// registerShared()测试
$container->registerShared('bbb', 'Foo');
$b1 = $container['bbb'];
echo PHP_EOL . $b1->num;    // 18   <--第一次生成
$b2 = $container['bbb'];
echo PHP_EOL . $b2->num;    // 18   <--第二次还是得到第一次生成的那个
```

### 2. 传入实例的测试

```php
<?php
// 这个一个简单的测试类
class Foo {
    public $num;
    public function __construct(){
        $this->num = rand(1, 100);
    }
}

require(__DIR__ . '/../soil/src/Container.php');

$container = new Soil\Container;

$foo = new Foo();
echo PHP_EOL . $foo->num;   // 74

// register()
$container->register('aaa', $foo);
$a1 = $container['aaa'];
echo PHP_EOL . $a1->num;    // 74   <--就是$foo
$a2 = $container['aaa'];
echo PHP_EOL . $a2->num;    // 74   <--就是$foo

// registerShared()
$container->register('bbb', $foo);
$b1 = $container['bbb'];
echo PHP_EOL . $b1->num;    // 74   <--还是$foo
$b2 = $container['bbb'];
echo PHP_EOL . $b2->num;    // 74   <--还是$foo
```

### 3. 传入闭包的测试

```php
<?php
// 这个一个简单的测试类
class Foo {
    public $num;
    public function __construct(){
        $this->num = rand(1, 100);
    }
}

require(__DIR__ . '/../soil/src/Container.php');

$container = new Soil\Container;

// register()
$container->register('aaa', function() {
    return new Foo;
}); // 注册为闭包函数
$a1 = $container['aaa'];
echo PHP_EOL . $a1->num;    // 40 <- 第一次get生成
$a2 = $container['aaa'];
echo PHP_EOL . $a2->num;    // 61 <- 第二次get又生成了新的

// registerShared()
$container->registerShared('bbb', function() {
    return new Foo;
}); // 注册为闭包函数
$b1 = $container['bbb'];
echo PHP_EOL . $b1->num;    // 72 <- 第一次生成
$b2 = $container['bbb'];
echo PHP_EOL . $b2->num;    // 72 <- 第二次还是得到第一次生成的那个
```

--------
参见 <https://github.com/maccliu/soil>