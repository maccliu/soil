# Soil\Debug

[TOC]

## dumpVar()

输出一个格式优化过的变量定义，方便检查和调试。

### 特点：

* 对数组变量进行了特别排版处理，使之显示更清晰。
* `NULL` 显示为小写的 `null`。
* `TRUE`、`FALSE` 显示为小写的 `true`,`false`。
* 对对象型的变量没有做处理，直接调用 var_export() 输出。


### 用法：

```php
require 'path/to/soil/Debug.php';

use Soil\Debug;

$userdata = [
    'A'=> 'a',
    'B'=> [],
    'C',
    'D'=> null,
    'F'=> true,
    ];
$_SERVER['user'] = $userdata;

echo Debug::dumpVar($_SERVER, '$_SERVER');
```

将会输出：

```php
$_SERVER = [
    'MIBDIRS'                        => '/xampp/php/extras/mibs',
    'MYSQL_HOME'                     => '\\xampp\\mysql\\bin',
    'OPENSSL_CONF'                   => '/xampp/apache/bin/openssl.cnf',
    'PHP_PEAR_SYSCONF_DIR'           => '\\xampp\\php',
    'PHPRC'                          => '\\xampp\\php',
    'TMP'                            => '\\xampp\\tmp',
    'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
	/* ... 省略若干行 ...*/
    'REQUEST_TIME'                   => 1483938200,
    'user'                           => [
                                            'A' => 'a',
                                            'B' => [],
                                            0   => 'C',
                                            'D' => null,
                                            'F' => true,
                                        ],
];
```