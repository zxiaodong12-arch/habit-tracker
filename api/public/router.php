<?php
// 用于 PHP 内置服务器的路由脚本：
//  - 如果请求的物理文件存在（如静态资源），直接返回该文件
//  - 其它所有请求都交给 index.php（交由 ThinkPHP 处理）

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    // 让内置服务器直接处理静态文件
    return false;
}

// 交给框架入口处理（等价于 Nginx 中的 rewrite 到 index.php）
require __DIR__ . '/index.php';

