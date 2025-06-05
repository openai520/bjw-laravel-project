# BJW Laravel 项目

这是一个基于Laravel的电商项目，包含产品管理、购物车、询价等功能。

## 功能特性

- 产品展示和管理
- 购物车功能
- 询价系统
- 管理后台
- 产品浏览统计
- 多语言支持

## 安装说明

### 环境要求

- PHP >= 8.0
- Composer
- Node.js & NPM
- MySQL/MariaDB

### 安装步骤

1. 克隆项目
```bash
git clone [your-repository-url]
cd bjw-laravel-project
```

2. 安装PHP依赖
```bash
composer install
```

3. 安装前端依赖
```bash
npm install
```

4. 配置环境变量
```bash
cp .env.example .env
```
编辑`.env`文件，配置数据库连接等信息。

5. 生成应用密钥
```bash
php artisan key:generate
```

6. 运行数据库迁移
```bash
php artisan migrate
```

7. 编译前端资源
```bash
npm run build
```

## 使用说明

- 前台页面：访问根路径
- 管理后台：访问 `/admin`

## 技术栈

- Laravel Framework
- MySQL
- Tailwind CSS
- Vite
- 中文本地化

## 许可证

此项目采用 MIT 许可证。 