# \Soil\Settings

Settings类主要是管理配置项。

用法：

```php
$foo = new Settings();
$foo->set('配置项名字', 配置项值);    // 用对象模式设置
$foo['配置项名字'] = 配置项值;        // 用数组模式设置

$kow = $foo->get('配置项名字');       // 用对象模式取值
$kow = $foo['配置项名字'];            // 用数组模式取值
```

最佳实践是把配置项分组：

```php
// 以下都是db组的配置项
$env['db.driver'] = 'mysql';
$env['db.user'] = 'foo';
$env['db.password'] = 'bar';
```

--------
参见 <https://github.com/maccliu/soil>