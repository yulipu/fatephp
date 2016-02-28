#一个自己用的php框架
## 本程序采用单一入口 一个控制器只有一个入口方法的理念开发
##未完成状态

###### 系统内置别名
* @y  系统目录
* @app  项目目录 由 appPath 指定
* @runtime  缓存目录 指向 @app/runtime

###### 项目目录结构
<pre>
app
  |
  --- controllers 普通控制器目录
    |
    |--- user 用户组
    |   |
    |   |--- IndexController.php 用户组下的控制器
    |   |--- OtherController.php
    |
    |--- goods 商品组
    |   |
    |   |--- IndexController.php
    |   |--- OtherController.php
    |
  --- views 普通控制器模板目录
    |
    |--- user 用户模板 对应上面用户组
    |   |
    |   |--- index.php
    |   |--- other.php
    |
    --- goods 商品模板
    |   |
    |   |--- index.php
    |   |--- other.php
    |
  --- modules 模块
    |
    |--- reg
    |   |
    |   |--- controllers 模块控制器目录 其下无子目录
    |   |   |
    |   |   |--- IndexController.php
    |   |
    |   |--- views 模块模板目录
    |   |   |
    |   |   |--- index.php
    |   |
    |   |--- 其他目录
    |
  --- runtime 缓存目录
    |
</pre>

```php
index.php

<?php
require(__DIR__ . '/system/Y.php');

$res = (new y\web\Application([
	'id'=>1, 
	'appPath'=> __DIR__ . '/app',
	'modules' => [
		'reg' => 'app\\modules\\reg'
	],
    'routes' => [
        // 把下面这个模式 路由到 IndexController 并且参数的键定义为 id
        '/p/(\d+)' => ['controllerId' => 'index', 'params' => ['key' => 'id', 'segment'=>1]]
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
            'class' => 'y\cache\file\Cache'
        ]
    ]
	
]))->run();
```

```php
app\controllers\index\IndexController.php

<?php
namespace app\controllers\index;

use y\db\DbFactory;
use y\cache\CacheFactory;

class IndexController extends \y\web\Controller {
	
	public function execute() {
        $this->render('index', []);
        
		//$db = DbFactory::instance('main');
        //$db->on($db::EVENT_BEFORE_QUERY, function(){
        //    echo 'beforeQuery<br>';
        //});
        
		//$sql = 'SELECT * FROM users';
		//foreach ($db->querySql($sql) as $row) {
        //   var_dump($row);
		//}
        
        //$res = $db->table('users')->where('1=1')->orderBy('id desc')->getAll();
        //$res2 = $db->table('users')->orderBy('id desc')->limit('2')->getAll();
        
        //$data = [
        //    ['name'=>'zhangsan', 'age'=>20, 'b'=>'ajdsfafj'],
        //    ['name'=>'wangwu', 'age'=>'20', 'b'=>'111'],
        //    ['name'=>'lisu', 'age'=>20, 'b'=>'ggggg']
        //];
        //$c = $db->table('users')->insert($data);
        
        //$c = $db->table('users')->where('id=1')->delete();
        
        //$data = ['name'=>'abc', 'age'=>1];
        //$db->table('users')->where('id=1')->update($data);
        
        //echo $db->table('users')->count();
        
        //$data = $db->table('users')->getAll();
        //var_dump($data);
        
        //$c = CacheFactory::instance('file');
        //$c->set('key', '123123');
        
        //echo $c->get('key');
        
        //$c->delete('key');
       
	}
}
```
