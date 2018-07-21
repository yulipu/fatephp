# 小型 php 框架 非线程安全

###### php 版本

+ php5.4+
+ php7

###### 变更

+ v2.0.0 修改框架名 yphp 更名为 fatephp 所有类路径 有 y\xxx 变为 fate\xxx
+ v1.3.0 更改 y\helpers\LinkedQueue 到 y\util\LinkedQueue
+ v1.3.0 修改 y\web\Request::getParam() 为 y\web\Request::getQueryString()
+ v1.3.0 修改 y\web\Request::postParam() 为 y\web\Request::getParameter()
+ v1.3.0 修改 y\web\Controller::view 的获取方式 $this->view 为 $this->getView()

###### 本程序特点

+ 采用控制器单一入口执行程序 解决单一文件过大不好维护问题
+ 以控制器目录分组的方式组织代码 结构清晰 支持无限级子目录 (模块控制器除外)

###### 系统内置别名

+ @fate  系统目录
+ @app  项目目录 由 appPath 指定
+ @runtime  缓存目录 指向 @app/runtime

###### 项目目录结构

<pre>
|- index.php
|
|- public 目录
|
|- app 项目目录
|  |
|  |-- controllers 普通控制器目录
|      |
|      |-- user 用户组目录
|      |   |
|      |   |-- IndexController.php 用户组下的控制器
|      |   |-- OtherController.php
|      |
|      |-- goods 商品组目录
|      |   |
|      |   |-- IndexController.php
|      |   |-- OtherController.php
|      |
|   -- views 普通控制器模板目录
|      |
|      |-- user 用户组模板 对应上面用户组
|      |   |
|      |   |-- index.php
|      |   |-- other.php
|      |
|   -- goods 商品组模板
|      |   |
|      |   |-- index.php
|      |   |-- other.php
|      |
|   -- modules 模块
|      |
|      |-- reg
|      |   |
|      |   |-- controllers 模块控制器目录 其下无子目录
|      |   |   |
|      |   |   |-- IndexController.php
|      |   |
|      |   |-- views 模块模板目录
|      |   |   |
|      |   |   |-- index.php
|      |   |
|      |   |-- 其他目录
|      |
|   -- runtime 缓存目录
|
</pre>

```php
路由格式

/index.php?r=[route_prefix|moduleId]/[controllerId]
```

```php
index.php

<?php
require(__DIR__ . '/system/Fate.php');

$res = (new fate\web\Application([
    'id'=>1, 
    'appPath'=> __DIR__ . '/app',
    'modules' => [
        'reg' => 'app\\modules\\reg'
    ],
    'db' => [
        'main' => [
            'dsn' => 'mysql:host=localhost;dbname=xxx',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'prefix'=> ''
        ]
    ],
    'cache' => [
        'file' => [
            'class' => 'fate\cache\file\Cache'
        ]
    ],
    'log' => [
        'targets' => [
            'file' => [
                'class' => 'fate\log\file\Log'
            ]
        ]
    ]
	
]))->run();
```

###### Db

```
$db = Db::instance('xxx');

// getAll
$data = $db->prepareSql('select title from xxx')->queryAll();

// getOne
$data = $db->prepareSql('select title from xxx limit 0, 1')->queryOne();

// single column
$n = $db->prepareSql('select count(id) from xxx')->queryColumn();

// with params
$data = $db->prepareStatement('select name from xxx where id = :id limit 0, 1')->bindValue(':id', 1)->queryOne();

$n = $db->prepareStatement('update xxx set username = ?')->bindValues(["li's"])->execute();

// 使用查询生成器
$data = $db->createQuery()->select('id,title')->from('xxx')->getAll();

$data = $db->createQuery()->select('id,title')->from('xxx')->where('id = ?', [1])->getOne();
$data = $db->createQuery()->select('id,title')->from('xxx')->where('id = :id', [':id' => 1])->getOne();

$n = $db->createQuery()->from('xxx')->where('id > 2')->count('id');
```
