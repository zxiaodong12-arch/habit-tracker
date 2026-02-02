# 部署到腾讯云 COS + CDN

## 为什么选择 COS？

- ✅ 国内访问速度快
- ✅ 可以自定义响应头（解决下载问题）
- ✅ 可以配置 CDN 加速
- ✅ 价格便宜

## 部署步骤

### 1. 创建 COS 存储桶

1. 登录腾讯云控制台
2. 对象存储 COS → 创建存储桶
3. 选择：
   - **地域**：选择离你最近的地域
   - **访问权限**：公有读私有写
   - **静态网站**：开启

### 2. 配置静态网站

1. 存储桶 → 基础配置 → 静态网站
2. **索引文档**：`index.html`
3. **错误文档**：`index.html`
4. **保存**

### 3. 配置自定义响应头（解决下载问题）

1. 存储桶 → 基础配置 → 自定义 Headers
2. 添加规则：
   - **文件类型**：`*.html`
   - **Header 名称**：`Content-Type`
   - **Header 值**：`text/html; charset=utf-8`
   - **操作**：覆盖（覆盖默认的 Content-Disposition）

### 4. 上传文件

```bash
# 构建
cd vue-frontend
npm run build

# 使用 COS CLI 上传
pip install coscmd
coscmd config -a SecretId -s SecretKey -b 存储桶名 -r 地域
coscmd upload -rs dist/ /
```

### 5. 配置 CDN（可选）

1. CDN 控制台 → 添加域名
2. 源站选择 COS 存储桶
3. 配置 HTTPS 证书
4. 完成

## 完成

访问 CDN 域名或 COS 域名，应该可以正常显示，不会下载。
