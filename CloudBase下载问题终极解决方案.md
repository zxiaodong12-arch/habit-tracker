# CloudBase index.html 下载问题 - 终极解决方案

## 问题确认

即使访问根路径 `/`，CloudBase 也返回 `Content-Disposition: attachment`，导致浏览器下载文件。

这是 **CloudBase 的默认行为**，不是配置错误。

## 解决方案

### 方案一：使用 CloudBase CLI 上传（推荐）

使用 CLI 上传可能不会触发下载行为：

```bash
# 1. 安装 CloudBase CLI
npm install -g @cloudbase/cli

# 2. 登录
tcb login

# 3. 删除旧文件
tcb hosting delete index.html

# 4. 上传新文件
cd vue-frontend
npm run build
tcb hosting deploy dist
```

### 方案二：检查 CloudBase 文件类型设置

1. **登录 CloudBase 控制台**
2. **静态网站托管 → 文件管理**
3. **检查 `index.html` 的文件类型**
   - 右键点击文件 → 查看属性
   - 确保文件类型是 `text/html`
   - 如果显示为其他类型，删除并重新上传

### 方案三：修改文件名（临时方案）

如果 CloudBase 对 `.html` 文件有特殊处理，可以尝试：

1. **将 `index.html` 重命名为 `index`（无扩展名）**
2. **在 CloudBase 控制台配置默认文档为 `index`**

但这个方法可能不工作，因为 CloudBase 需要 `.html` 扩展名。

### 方案四：使用其他静态托管（如果 CloudBase 无法解决）

如果 CloudBase 确实无法解决，可以考虑：

1. **腾讯云 COS + CDN**
   - 配置更灵活
   - 可以自定义响应头

2. **GitHub Pages / Netlify / Vercel**
   - 免费
   - 对 Vue 项目支持更好

### 方案五：联系 CloudBase 技术支持

这是 CloudBase 平台的问题，可能需要技术支持解决：

1. **提交工单**
   - 说明问题：静态网站托管的 HTML 文件被下载而不是显示
   - 提供响应头：`Content-Disposition: attachment`
   - 提供域名和测试 URL

2. **询问是否有配置选项**
   - 是否可以禁用 `Content-Disposition: attachment`
   - 是否可以自定义响应头

## 临时解决方案

在等待 CloudBase 修复期间，可以：

### 使用浏览器扩展

某些浏览器扩展可以强制显示 HTML 文件而不是下载，但这不是理想的解决方案。

### 使用代理服务器

使用 Nginx 等反向代理，可以修改响应头：

```nginx
server {
    listen 80;
    server_name your-proxy.com;
    
    location / {
        proxy_pass http://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com;
        proxy_hide_header Content-Disposition;
    }
}
```

## 诊断步骤

### 1. 检查文件上传方式

```bash
# 使用 curl 检查
curl -I http://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com/

# 查看完整的响应头
curl -v http://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com/ 2>&1 | grep -i "content"
```

### 2. 检查文件内容

```bash
# 下载文件查看
curl http://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com/ > test.html
file test.html
# 应该显示：HTML document
```

### 3. 尝试不同的访问方式

- 直接访问：`http://域名/`
- 带路径：`http://域名/#/login`
- 带 index.html：`http://域名/index.html`（会下载）

## 最可能的原因

1. **CloudBase 的默认行为**
   - 某些配置或版本可能默认设置 `Content-Disposition: attachment`
   - 需要联系技术支持修改

2. **文件上传方式**
   - 通过控制台上传可能触发下载行为
   - 使用 CLI 上传可能不同

3. **CloudBase 版本或配置**
   - 不同环境可能有不同行为
   - 需要检查环境配置

## 推荐行动

1. **首先尝试方案一**：使用 CloudBase CLI 重新上传
2. **如果不行，联系 CloudBase 技术支持**：这是平台问题
3. **如果急需解决，考虑方案四**：使用其他静态托管服务

## 总结

**问题：** CloudBase 返回 `Content-Disposition: attachment`，导致 HTML 文件被下载

**根本原因：** CloudBase 平台的默认行为或配置问题

**解决方案：**
1. 使用 CLI 上传（可能不同）
2. 联系技术支持
3. 考虑其他托管服务
