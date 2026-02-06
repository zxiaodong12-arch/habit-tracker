<?php
// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

// 在 PHP 8.5 下屏蔽 deprecated 级别（不影响真正的错误/警告）
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
