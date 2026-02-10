# ThinkPHP 6 → 8 升级说明

本项目已完成从 ThinkPHP 6.1 到 8.x 的依赖升级。

## 已修改

- **composer.json**
  - `php`: `>=7.2.5` → `>=8.0.0`
  - `topthink/framework`: `^6.1.0` → `^8.0`
  - `topthink/think-orm`: `^2.0` → `^3.0`

## 本地 / 服务器操作

1. 确保 PHP 版本 ≥ 8.0：`php -v`
2. 在 `api` 目录执行：
   ```bash
   rm -f composer.lock
   composer update
   ```
3. 用浏览器或 Postman 访问接口（如健康检查、登录、习惯列表），确认功能正常。

## 若出现错误

- **控制器/中间件相关**：TP8 控制器依赖注入方式未变，当前 `BaseController` 与中间件写法可继续使用。
- **ORM 报错**：think-orm 3.x 与 2.x 大部分用法兼容，若某处报错可查 [ThinkPHP 8 文档](https://doc.thinkphp.cn/) 或 think-orm 3.0 变更说明。
- **配置**：若某配置项报「不存在」或弃用，可对照 [官方升级指南](https://doc.thinkphp.cn/) 做小幅调整。

## 与 PHP 8.5 的兼容

若服务器使用 PHP 8.5，建议将框架锁定到 8.1.x（已做 PHP 8.5 兼容）：

```json
"topthink/framework": "^8.1"
```

当前 `^8.0` 已可安装 8.1.4，无需改 composer.json；若要显式锁定 8.1：`composer require topthink/framework:^8.1`。
