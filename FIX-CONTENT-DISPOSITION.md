# 修复 index.html 被下载的问题

## 问题原因

响应头中有 `Content-Disposition: attachment`，这会导致浏览器下载文件而不是显示。

## 解决方案

### 方案一：检查 CloudBase 配置（最重要）

1. **登录 CloudBase 控制台**
   - https://console.cloud.tencent.com/tcb

2. **检查静态网站托管配置**
   - 静态网站托管 → 基础配置
   - 查找是否有"下载"或"Content-Disposition"相关的设置
   - 如果有，关闭或删除

3. **检查文件上传方式**
   - 确保文件是通过"上传"而不是"下载"方式上传的
   - 某些上传工具可能会设置 Content-Disposition

### 方案二：删除并重新上传文件

1. **删除 CloudBase 上的 index.html**
   - CloudBase 控制台 → 静态网站托管 → 文件管理
   - 删除 `index.html`

2. **重新构建**
   ```bash
   cd vue-frontend
   npm run build
   ```

3. **重新上传 index.html**
   - 确保使用"上传文件"功能
   - 不要使用"下载"或"导入"功能

### 方案三：使用 CloudBase CLI 上传

如果控制台上传有问题，使用 CLI：

```bash
# 安装 CloudBase CLI
npm install -g @cloudbase/cli

# 登录
tcb login

# 删除旧文件
tcb hosting delete index.html

# 上传新文件
tcb hosting deploy vue-frontend/dist
```

### 方案四：检查文件元数据

某些文件可能在上传时被标记了下载属性：

1. **检查本地文件**
   ```bash
   cd vue-frontend/dist
   ls -la index.html
   # 确保文件权限正常
   ```

2. **重新创建文件**
   ```bash
   # 如果文件有问题，重新构建
   cd vue-frontend
   rm -rf dist
   npm run build
   ```

## 快速修复步骤

### 步骤 1: 删除旧文件

在 CloudBase 控制台：
1. 静态网站托管 → 文件管理
2. 找到 `index.html`
3. 删除它

### 步骤 2: 重新构建

```bash
cd vue-frontend
npm run build
```

### 步骤 3: 重新上传

1. 在 CloudBase 控制台 → 文件管理
2. 点击"上传文件"
3. 选择 `dist/index.html`
4. 确保上传到根目录 `/`

### 步骤 4: 验证

```bash
# 检查响应头
curl -I http://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com/

# 不应该看到：Content-Disposition: attachment
# 应该看到：Content-Type: text/html
```

## 如果问题仍然存在

### 检查 CloudBase 设置

1. **检查是否有全局下载设置**
   - CloudBase 控制台 → 静态网站托管 → 设置
   - 查找"下载"、"附件"或"Content-Disposition"相关选项

2. **检查文件类型设置**
   - 确保 HTML 文件没有被设置为"下载"类型

3. **联系 CloudBase 技术支持**
   - 说明问题：`index.html` 被下载而不是显示
   - 提供响应头信息：`Content-Disposition: attachment`

## 临时解决方案

如果无法立即修复，可以：

1. **使用其他路径访问**
   - 尝试：`http://域名/#/login`（Hash 路由）
   - 可能不会触发下载

2. **修改浏览器设置**（不推荐）
   - 某些浏览器可以设置不自动下载 HTML
   - 但这不是根本解决方案

## 验证修复

修复后，检查：

```bash
curl -I http://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com/
```

**应该看到：**
```
Content-Type: text/html
（没有 Content-Disposition: attachment）
```

**不应该看到：**
```
Content-Disposition: attachment
```

## 总结

**问题原因**：CloudBase 返回了 `Content-Disposition: attachment` 响应头

**解决方法**：
1. 删除并重新上传 `index.html`
2. 检查 CloudBase 配置
3. 确保文件上传方式正确

**最快方法**：删除旧文件，重新构建，重新上传。
