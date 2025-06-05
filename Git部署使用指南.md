# Git部署使用指南 📋

## 🎯 部署已完成！

您的项目已经成功配置为Git部署模式。以下是日常使用方法：

## 🔄 日常更新流程

### 1. 本地开发并推送到GitHub

```bash
# 在本地项目目录 (/Users/lailai/Desktop/bjw-laravel-project)
git add .
git commit -m "描述您的更改"
git push origin main
```

### 2. 服务器更新部署

```bash
# 一键部署命令
ssh -i ~/Desktop/sursor.pem root@43.165.63.73 "/www/wwwroot/git_deploy.sh"
```

## 📂 重要文件说明

### 服务器配置
- **项目路径**: `/www/wwwroot/kalala.me`
- **部署脚本**: `/www/wwwroot/git_deploy.sh`
- **备份路径**: `/www/wwwroot/backups`

### 环境配置保护
- `.env.server_backup` - 服务器环境配置（自动保护）
- 每次部署会自动备份当前`.env`文件

## 🚀 部署脚本功能

Git部署脚本会自动完成：

1. ✅ 备份环境配置文件
2. ✅ 拉取GitHub最新代码
3. ✅ 恢复服务器特定的环境配置
4. ✅ 更新Composer依赖
5. ✅ 运行数据库迁移
6. ✅ 清除和重建缓存
7. ✅ 设置正确的文件权限
8. ✅ 重启PHP服务

## 📊 优势对比

| 功能 | 旧方式(SCP) | 新方式(Git) |
|------|-------------|-------------|
| 版本控制 | ❌ | ✅ 完整历史记录 |
| 一键部署 | ❌ | ✅ 一条命令搞定 |
| 回滚能力 | ❌ | ✅ 可回滚任意版本 |
| 冲突处理 | ❌ | ✅ 自动处理 |
| 增量更新 | ❌ | ✅ 只传输变更 |
| 自动化 | ❌ | ✅ 脚本自动化 |

## 🔧 高级操作

### 查看部署日志
```bash
# 查看Git日志
ssh -i ~/Desktop/sursor.pem root@43.165.63.73 "cd /www/wwwroot/kalala.me && git log --oneline -10"
```

### 回滚到指定版本
```bash
# 查看版本历史
ssh -i ~/Desktop/sursor.pem root@43.165.63.73 "cd /www/wwwroot/kalala.me && git log --oneline -10"

# 回滚到指定提交（替换<commit-hash>为实际的提交哈希）
ssh -i ~/Desktop/sursor.pem root@43.165.63.73 "cd /www/wwwroot/kalala.me && git reset --hard <commit-hash> && /www/wwwroot/git_deploy.sh"
```

### 紧急恢复
```bash
# 如果Git部署出现问题，可以恢复到之前的备份
ssh -i ~/Desktop/sursor.pem root@43.165.63.73 "cd /www/wwwroot && ls -la | grep kalala.me_backup"
# 选择合适的备份目录进行恢复
```

## 🔒 安全说明

1. **环境变量保护**: 部署时会自动保护服务器的`.env`配置
2. **权限管理**: 自动设置正确的文件权限
3. **备份机制**: 每次部署前自动备份

## 📞 故障排除

### 如果Git拉取失败：
```bash
# 检查网络连接
ssh -i ~/Desktop/sursor.pem root@43.165.63.73 "ping github.com"

# 检查Git配置
ssh -i ~/Desktop/sursor.pem root@43.165.63.73 "cd /www/wwwroot/kalala.me && git remote -v"
```

### 如果权限问题：
```bash
# 重新设置权限
ssh -i ~/Desktop/sursor.pem root@43.165.63.73 "cd /www/wwwroot/kalala.me && chown -R www:www . && chmod -R 755 ."
```

## 🎊 恭喜！

您已经成功将项目从SCP部署升级到Git部署！
现在您可以享受现代化的代码部署体验了。

---
*最后更新时间: 2025年6月5日* 