# Soil\Match 匹配

`Match`主要用来匹配一个字符串是否满足特定格式要求。

## isStartWith() 和 isEndWith() 以...开始/结束

```php
public static function isStartWith($subject, $find, $ignore_case = false);
public static function isEndWith($subject, $find, $ignore_case = false);
```

## namedParamters() 匹配命名参数

```php
function namedParamters($subject, $rule, &$matches, $rule_vars = null, $ignore_case = false);
```

### 用法一：基本模式。

如果不指定$rule中的变量，则默认变量为`{` `变量名` `}`的形式：

```php
use \Soil\Match;

$subject = '/user/profile/edit/id/5';
$rule = '/{module}/{controller}/{action}/';       // 定义$rule时，顺带就定义了三个变量
$matches = [];

$result = Match::rule($subject, $rule, $matches);
var_dump($result, $matches);

/*
$result  = int(1);
$matches = [
    "{module}"     => "user",
	"{controller}" => "profile",
	"{action}"     => "edit",
];
*/
```

### 用法二：文艺模式。

只指定变量名，不指定变量的正则表达式（可用null代替），则将$rule表达式用这些指定的变量名匹配。

```php
use \Soil\Match;

$subject = '/user/profile/edit/id/5';
$rule = '/module/controller/action/';
$matches = [];
$rule_vars = [
    'module'     => null,
    'controller' => null,
    'action'     => null,
];

$result = Match::rule($subject, $rule, $matches, $rule_vars);
var_dump($result, $matches);

/*
$result  = int(1);
$matches = [
    "module"     => "user",
	"controller" => "profile",
	"action"     => "edit",
];
*/
```

这里可能有个坑要注意一下：

```php
$subject = '/user/profile/edit/id/5';
$rule = '/module/controller/action/';
$rule_vars = ['module' => null, 'controller' => null, 'action' => null,];
```
上面的`$subject`可以匹配`$rule`，但是如果少定义了一个，比如说少定义了action：

```php
$rule_vars = ['module' => null, 'controller' => null,];
```

那就悲剧了：`$subject`是不能匹配`$rule`的。原因是`/action/`此时已经变成固定要匹配的字符串了：

```php
$subject1 = '/user/profile/edit/id/5';
$rule = '/{module}/{controller}/action/';       <-- 看懂了吗？

$subject2 = '/user/profile/action/id/5';        <-- $subject2是可以匹配的
$rule = '/{module}/{controller}/action/';
```

**所以，最佳实践是：还是用大括号的形式（`{变量名}`）来表示`$rule`中的变量最靠谱，不容易出错！**

如果`$rule_vars`给的是一个空数组，则等于只比较$subject是否是以$rule开始。

```php
use \Soil\Match;

$matches = [];
$rule = '/module/controller/action/';
$rule_vars = [];

$subject1 = '/user/profile/edit/id/5';
$subject2 = '/module/controller/action/id/5';

$result1 = Match::rule($subject1, $rule, $matches, $rule_vars);    // false
$result2 = Match::rule($subject2, $rule, $matches, $rule_vars);    // true
```

### 用法三：黑客模式。

变量的正则模板也自己设置。基本能灵活应付95%以上的网站匹配需求了。

```php
use \Soil\Match;

$rule = '/{module}/{controller}/{action}/{id}';
$rule_vars = [
    '{module}'     => null,
    '{controller}' => null,
    '{action}'     => null,
	'{id}'         => '\d+',
];

$matches1 = [];
$matches2 = [];

$subject1 = '/user/profile/edit/abcd';
$subject2 = '/user/profile/edit/1234';

$result1 = Match::rule($subject1, $rule, $matches1, $rule_vars);    // false
$result2 = Match::rule($subject2, $rule, $matches2, $rule_vars);    // true
var_dump($result1, $matches1, $result2, $matches2);

/*
$result1 = 0;        // $subject1 不匹配！
$matches1 = [];

$result2 = 1;        // $subject2 匹配！
$matches2 = [
    "{module}"     => "user",
    "{controller}" => "profile",
    "{action}"     => "edit",
    "{id}"         => '1234',
];
*/
```

随便说下，上面的例子中，对如下的匹配主题，`{id}`匹配到的都是`1234`。

```php
$subject2 = '/user/profile/edit/1234';
$subject2 = '/user/profile/edit/1234abcd';
$subject2 = '/user/profile/edit/1234-some word';
```

--------
参见 <https://github.com/maccliu/soil>