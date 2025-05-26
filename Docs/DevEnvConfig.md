# BJW Laravel E-commerce 开发环境配置

## 系统要求
- PHP >= 8.1
- Composer
- Node.js >= 16.x
- NPM/Yarn
- Git
- **云数据库连接** (MySQL 8.0+)

## 环境搭建步骤

### 1. 克隆项目
```bash
git clone [项目仓库地址]
cd bjw-laravel-project
```

### 2. 安装PHP依赖
```bash
composer install
```

### 3. 环境配置
```bash
# 复制环境配置文件
cp .env.example .env

# 生成应用密钥
php artisan key:generate
```

### 4. 云数据库配置
在 `.env` 文件中配置云数据库连接：
```env
DB_CONNECTION=mysql
DB_HOST=云数据库IP地址
DB_PORT=3306
DB_DATABASE=bjw_laravel
DB_USERNAME=云数据库用户名
DB_PASSWORD=云数据库密码

# 云数据库连接超时设置
DB_TIMEOUT=60
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

**注意**: 请联系项目管理员获取云数据库的连接信息

### 5. 数据库连接测试
```bash
# 测试数据库连接
php artisan tinker
# 在tinker中执行: DB::connection()->getPdo();

# 运行迁移（如果是首次设置）
php artisan migrate

# 运行种子数据（如果需要）
php artisan db:seed
```

### 6. 存储链接
```bash
php artisan storage:link
```

### 7. 前端资源构建
```bash
# 安装Node.js依赖
npm install

# 开发环境构建
npm run dev

# 生产环境构建
npm run build
```

### 8. 权限设置
```bash
# 设置存储目录权限
chmod -R 775 storage bootstrap/cache
```

## 云部署环境

### 生产服务器信息
- **服务器IP**: 43.165.63.73
- **Web服务器**: Nginx
- **PHP版本**: 8.1+
- **部署脚本**: `deploy.sh` 和 `upload_css_updates.sh`

### 部署命令
```bash
# 部署代码到云服务器
./deploy.sh

# 部署CSS更新
./upload_css_updates.sh

# 在云服务器上优化
ssh root@43.165.63.73 "cd /var/www/bjw-laravel-project && php artisan optimize"
```

## 常用开发命令

### Laravel命令
```bash
# 启动开发服务器
php artisan serve

# 清除缓存
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 优化
php artisan optimize
php artisan optimize:clear

# 数据库相关
php artisan migrate
php artisan migrate:status
php artisan migrate:rollback
```

### 前端开发
```bash
# 监听文件变化
npm run watch

# 热重载开发
npm run hot

# 构建生产版本
npm run build
```

## IDE配置
推荐使用 VS Code 或 PhpStorm

### VS Code 扩展推荐
- PHP Intelephense
- Laravel Blade Snippets
- Laravel Artisan
- Tailwind CSS IntelliSense
- Remote - SSH (用于云服务器连接)

### PhpStorm配置
- 配置数据库连接到云数据库
- 设置代码风格为PSR-12
- 配置Laravel插件

## 云数据库注意事项

### 1. 连接安全
- 确保本地IP在数据库白名单中
- 使用强密码和专用数据库用户
- 避免在公共网络下直接连接云数据库

### 2. 性能考虑
- 云数据库连接可能有轻微延迟
- 建议使用连接池优化
- 避免频繁的数据库连接操作

### 3. 数据同步
- 开发时直接操作云数据库，请谨慎处理数据
- 重要操作前先备份数据
- 使用数据库迁移管理结构变更

## 故障排除

### 常见问题
1. **权限问题**: 确保storage和bootstrap/cache目录有写权限
2. **依赖问题**: 检查PHP版本和扩展
3. **云数据库连接失败**: 
   - 检查网络连接
   - 确认IP白名单设置
   - 验证数据库凭证
   - 检查防火墙设置

### 数据库连接故障排除
```bash
# 测试网络连接
ping 云数据库IP

# 测试端口连通性
telnet 云数据库IP 3306

# Laravel数据库连接测试
php artisan tinker
>>> DB::connection()->getPdo()

# 查看数据库配置
php artisan config:show database
```

### 日志查看
```bash
# Laravel日志
tail -f storage/logs/laravel.log

# 查看数据库相关错误
grep -i database storage/logs/laravel.log

# 实时监控日志
tail -f storage/logs/laravel.log | grep -i error
```

## 环境变量说明

| 变量名 | 说明 | 示例值 |
|--------|------|--------|
| APP_ENV | 应用环境 | local/production |
| APP_DEBUG | 调试模式 | true/false |
| DB_HOST | 云数据库主机 | xxx.xxx.xxx.xxx |
| DB_PORT | 数据库端口 | 3306 |
| DB_DATABASE | 数据库名 | bjw_laravel |
| DB_USERNAME | 数据库用户名 | your_username |
| DB_PASSWORD | 数据库密码 | your_password |

## 云部署工作流

### 开发流程
1. **本地开发** → 连接云数据库进行开发
2. **功能测试** → 在本地环境测试功能
3. **代码提交** → 提交到Git仓库
4. **部署测试** → 使用部署脚本同步到云服务器
5. **生产验证** → 在生产环境验证功能

### 数据管理
- **开发数据**: 在云数据库中创建测试数据
- **生产数据**: 谨慎操作，避免误删除
- **数据备份**: 定期备份重要数据

## 备注
- 本地开发直接连接云数据库，确保数据一致性
- 生产环境使用相同的云数据库
- 部署脚本自动化处理代码同步和环境配置
- 定期更新依赖包和安全补丁
- 遵循云数据库的使用规范和安全策略
