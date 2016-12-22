# Soil\Singleton

Singleton类实现了单例模式。一个Singleton类只能返回类的唯一实例（`$_instance`）和一个获得该实例的静态方法（`getInstance()`）。

为实现上述目的，需要做如下处理：

1. 取得实例只能用`Singleton::getInstance()`的方式。
2. 要把`__construct()`加上private限定，阻止用new方式新建实例。
3. 要把`__clone()`加上private限定，阻止用clone方式新建实例。

--------
参见 <https://github.com/maccliu/soil>