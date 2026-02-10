# 链路验证清单

以下步骤用于验证域名解析、TLS、CORS、登录接口与前端调用链路是否正常。

## A. DNS/证书

```bash
nslookup api.legoapi.cn
```

```bash
openssl s_client -connect api.legoapi.cn:443 -servername api.legoapi.cn </dev/null | openssl x509 -noout -subject -issuer -dates
```

## B. API 健康检查（本地走代理）

```bash
curl --socks5-hostname 127.0.0.1:1081 https://api.legoapi.cn/health
```

## C. CORS 预检

```bash
curl -i -X OPTIONS \
  -H "Origin: https://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com" \
  -H "Access-Control-Request-Method: POST" \
  https://api.legoapi.cn/api/auth/login
```

期望：`204`，并带 `Access-Control-Allow-Origin` 等头（且只出现一次）。

## D. 登录接口（带 Origin）

```bash
curl -i -X POST https://api.legoapi.cn/api/auth/login \
  -H "Origin: https://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data "username=legolas&password=021271"
```

期望：`200`，JSON `success: true`，并包含单套 CORS 头。

## E. 前端页面验证

1. 打开页面：
```
https://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com/#/login
```
2. 登录后在 DevTools → Network → `login`  
   确认：
   - Status = `200`
   - Response Headers 有 `Access-Control-Allow-Origin`（且只有一条）

## F. 服务器端（EC2）

```bash
sudo nginx -t
```

```bash
sudo nginx -T | egrep -n "add_header|fastcgi_hide_header|server_name api.legoapi.cn"
```
