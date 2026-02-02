# 快速测试 CloudBase 问题

## 问题
访问根路径时，CloudBase 返回 `Content-Disposition: attachment`

## 测试命令
```bash
# 检查响应头
curl -I http://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com/

# 查看完整响应
curl -v http://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com/ 2>&1 | head -30
```

## 可能的解决方案
1. 使用 CloudBase CLI 重新上传
2. 联系 CloudBase 技术支持
3. 考虑使用其他静态托管服务
