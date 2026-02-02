# OpenCloudOS 安装 Nginx 指南

## 问题说明

OpenCloudOS（腾讯云基于 CentOS 的发行版）默认仓库可能不包含 Nginx，需要添加 EPEL 仓库或使用其他方法安装。

## 解决方案

### 方法一：使用 EPEL 仓库（推荐）

```bash
# 1. 安装 EPEL 仓库
yum install -y epel-release

# 2. 安装 Nginx
yum install -y nginx

# 3. 启动 Nginx
systemctl start nginx

# 4. 设置开机自启
systemctl enable nginx

# 5. 检查状态
systemctl status nginx
```

### 方法二：使用 Nginx 官方仓库（推荐，版本更新）

```bash
# 1. 创建 Nginx 仓库配置文件
cat > /etc/yum.repos.d/nginx.repo << 'EOF'
[nginx-stable]
name=nginx stable repo
baseurl=http://nginx.org/packages/centos/$releasever/$basearch/
gpgcheck=1
enabled=1
gpgkey=https://nginx.org/keys/nginx_signing.key
module_hotfixes=true
EOF

# 2. 安装 Nginx
yum install -y nginx

# 3. 启动 Nginx
systemctl start nginx

# 4. 设置开机自启
systemctl enable nginx

# 5. 检查状态
systemctl status nginx
```

### 方法三：使用腾讯云镜像源（如果方法一失败）

```bash
# 1. 备份原有仓库配置
mkdir -p /etc/yum.repos.d/backup
mv /etc/yum.repos.d/*.repo /etc/yum.repos.d/backup/ 2>/dev/null

# 2. 使用腾讯云镜像源（OpenCloudOS）
cat > /etc/yum.repos.d/opencloudos.repo << 'EOF'
[opencloudos]
name=OpenCloudOS
baseurl=https://mirrors.cloud.tencent.com/opencloudos/$releasever/os/$basearch/
enabled=1
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-OpenCloudOS

[opencloudos-updates]
name=OpenCloudOS Updates
baseurl=https://mirrors.cloud.tencent.com/opencloudos/$releasever/updates/$basearch/
enabled=1
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-OpenCloudOS

[epel]
name=Extra Packages for Enterprise Linux
baseurl=https://mirrors.cloud.tencent.com/epel/$releasever/$basearch/
enabled=1
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-EPEL-8
EOF

# 3. 清理缓存
yum clean all

# 4. 重新生成缓存
yum makecache

# 5. 安装 EPEL
yum install -y epel-release

# 6. 安装 Nginx
yum install -y nginx
```

## 验证安装

```bash
# 检查 Nginx 版本
nginx -v

# 检查 Nginx 状态
systemctl status nginx

# 测试 Nginx 配置
nginx -t

# 查看 Nginx 进程
ps aux | grep nginx
```

## 配置防火墙

```bash
# 开放 80 端口（HTTP）
firewall-cmd --permanent --add-service=http
firewall-cmd --reload

# 或开放 8080 端口（如果使用自定义端口）
firewall-cmd --permanent --add-port=8080/tcp
firewall-cmd --reload

# 查看开放的端口
firewall-cmd --list-ports
firewall-cmd --list-services
```

## 测试访问

```bash
# 在服务器上测试
curl http://localhost

# 如果看到 Nginx 欢迎页面，说明安装成功
```

## 常见问题

### Q: 仍然无法安装？

**A:** 尝试以下步骤：

```bash
# 1. 清理所有缓存
yum clean all

# 2. 更新仓库信息
yum makecache

# 3. 搜索 Nginx 包
yum search nginx

# 4. 如果还是找不到，使用编译安装（不推荐，但可行）
```

### Q: 如何卸载并重新安装？

```bash
# 卸载 Nginx
yum remove -y nginx

# 清理配置文件（可选）
rm -rf /etc/nginx

# 然后按照上面的方法重新安装
```

### Q: 安装后无法启动？

```bash
# 检查错误日志
journalctl -xe

# 检查端口占用
netstat -tlnp | grep :80

# 检查配置文件语法
nginx -t
```

## 下一步

安装成功后，继续按照 `DEPLOY-TENCENT-CLOUD.md` 的步骤配置 Nginx。
