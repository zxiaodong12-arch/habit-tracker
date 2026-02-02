# GitHub SSH 密钥设置完整指南

## 🔑 你的 SSH 公钥

```
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDkHit2il/f6oraL/GF/AqnyM776+qgrFE8YDeIqbNMZ8oEtPHVRSv/HV1I99yXWjLb117jopVcE6L0hGrQgw5dbyrYvJbF6+lK39auMc+AQJAtcWdUh8rFmDwM13nwU+QzNH5Jv7tXC6aFkfJvXyNbk72jh5QTl1D3nS8vZO+n2otKtSf+ExERseAeDcpBlSh0rQ4Q4papspkpz34OmSUx+HjPrfD6JJWE0KPR5hECdS7QruDaf7DE5+1BBndAQj/MjfH4c24dTjjkdSjuRLsCWnSKUXt+HSCbO3MYGYrincHftBcPkgyUCBySc2XaBPlJmAGC82BtFNmoNzuySvCaUxGMoWHSsicr4MP4LlqoXaYOtriFs/RkS+cZsnybUFQilrkx3Gti9R6VgwCFOk8Ktg7dzTaeu7T3q1/DirgYeBP2k3U4IvwGAQpb8XGId5rGAo3zh0sudJXE6tfSCE/KU/Nh5x1PIovH4g3xkZ1ONYJsiu76nPdWvEq1mkWH58wjkppE6Ah1/RdgOqjHwK2TYmsRG8iFJomkGbRx7QMb2WXlr26vHbCq7R0iU76aDpYKPrQlvTo0pNcbI2+VJ8aiw1XWfGHCPsFn1liIm2u0vjdqc0+By4W/oh5M/o+unurz34jBlVSFJTRxZMQPtd3L68VT7vDgBGsgIOYDr8qIHQ== xiaodong.zhang@sherpa.com.cn
```

## 📋 步骤 1：添加 SSH 密钥到 GitHub

### 方法 A：使用网页界面（推荐）

1. **打开 GitHub SSH 设置页面**
   - 直接访问：https://github.com/settings/keys
   - 或者：GitHub → 右上角头像 → Settings → SSH and GPG keys

2. **添加新密钥**
   - 点击绿色的 **"New SSH key"** 按钮
   - **Title**（标题）：输入 `MacBook Pro` 或任意名称
   - **Key type**（密钥类型）：选择 `Authentication Key`
   - **Key**（密钥内容）：粘贴上面的完整 SSH 公钥
     - 包括开头的 `ssh-rsa` 和结尾的邮箱
     - 确保是完整的一行，没有换行

3. **保存**
   - 点击 **"Add SSH key"** 按钮
   - 可能需要输入 GitHub 密码确认

### 方法 B：使用命令行（快速复制）

运行以下命令会自动打开浏览器并复制密钥：

```bash
# 复制 SSH 公钥到剪贴板
cat ~/.ssh/id_rsa.pub | pbcopy

# 打开 GitHub SSH 设置页面
open https://github.com/settings/keys
```

然后在网页中粘贴（Cmd+V）并保存。

## 📋 步骤 2：测试 SSH 连接

添加完成后，运行：

```bash
ssh -T git@github.com
```

**成功的话会显示：**
```
Hi zxiaodong12-arch! You've successfully authenticated, but GitHub does not provide shell access.
```

**如果还是失败，检查：**
1. 密钥是否完整复制（包括 `ssh-rsa` 开头和邮箱结尾）
2. 是否点击了 "Add SSH key" 保存
3. 等待几秒钟让 GitHub 更新

## 📋 步骤 3：切换到 SSH 并推送代码

SSH 连接成功后：

```bash
cd /System/Volumes/Data/data/RD/habit-tracker

# 切换到 SSH URL
git remote set-url origin git@github.com:zxiaodong12-arch/habit-tracker.git

# 推送代码
git push -u origin main
```

## 🚀 或者使用 Personal Access Token（更快）

如果 SSH 配置遇到问题，可以使用 Token 方式（约 2 分钟）：

### 1. 创建 Token

1. 访问：https://github.com/settings/tokens/new
2. **Note**（备注）：输入 `habit-tracker-deploy`
3. **Expiration**（过期时间）：选择 90 days 或 No expiration
4. **Select scopes**（权限）：勾选 `repo`（全部仓库权限）
5. 点击 **"Generate token"**（生成令牌）
6. **重要**：立即复制 token（类似 `ghp_xxxxxxxxxxxxxxxxxxxx`），只显示一次！

### 2. 使用 Token 推送

```bash
git push -u origin main
```

当提示输入密码时：
- **Username**: `zxiaodong12@gmail.com`
- **Password**: 粘贴你的 token（**不是 GitHub 密码！**）

## ❓ 常见问题

### Q: SSH 连接还是失败？

**A:** 尝试以下步骤：

1. **检查密钥格式**：确保是完整的一行，没有换行或空格
2. **重新添加密钥**：删除旧的，重新添加
3. **检查 SSH agent**：
   ```bash
   ssh-add ~/.ssh/id_rsa
   ssh -T git@github.com
   ```
4. **使用 Token 方式**：如果还是不行，直接用 Personal Access Token

### Q: 如何查看已添加的密钥？

**A:** 访问 https://github.com/settings/keys 查看所有已添加的 SSH 密钥

### Q: Token 和 SSH 有什么区别？

**A:** 
- **SSH 密钥**：一次配置，长期使用，更安全
- **Personal Access Token**：需要定期更新，但配置更快

## ✅ 完成后的下一步

推送成功后：

1. 进入仓库：https://github.com/zxiaodong12-arch/habit-tracker
2. 点击 **Settings** → **Pages**
3. 在 **Source** 选择 **GitHub Actions**
4. 保存设置
5. 等待几分钟，GitHub Actions 会自动构建并部署
6. 访问你的网站：`https://zxiaodong12-arch.github.io/habit-tracker`
