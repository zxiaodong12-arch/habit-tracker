<?php
// 应用配置文件
return [
    // 应用地址
    'app_host' => env('app.host', ''),
    // 应用的命名空间
    'app_namespace' => '',
    // 是否启用路由
    'with_route' => true,
    // 默认应用
    'default_app' => 'index',
    // 默认的控制器名
    'default_controller' => 'Index',
    // 默认的操作名
    'default_action' => 'index',
    // 是否强制使用路由
    'url_route_must' => false,
    // 合并路由规则
    'route_rule_merge' => false,
    // 路由是否完全匹配
    'route_complete_match' => false,
    // 是否去除URL中的html后缀
    'url_html_suffix' => '',
    // 访问控制器层名称
    'controller_layer' => 'controller',
    // 空控制器名
    'empty_controller' => 'Error',
    // 操作方法后缀
    'action_suffix' => '',
    // 默认的路由变量规则
    'default_route_pattern' => '[\w\.]+',
    // 是否开启请求缓存
    'request_cache' => false,
    // 请求缓存有效期
    'request_cache_expire' => null,
    // 全局请求缓存排除规则
    'request_cache_except' => [],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => app()->getThinkPath() . 'tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl' => app()->getThinkPath() . 'tpl/dispatch_jump.tpl',
    // 异常页面的模板文件
    'exception_tmpl' => app()->getThinkPath() . 'tpl/think_exception.tpl',
    // 错误显示信息,非调试模式有效
    'error_message' => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg' => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle' => '',
    // 应用调试模式
    'app_debug' => env('app.APP_DEBUG', true),
];
