#!/bin/bash

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

PROJECT_PATH="/www/wwwroot/kalala.me"
BACKUP_PATH="/www/wwwroot/backups"

echo -e "${YELLOW}开始Git部署...${NC}"

# 创建备份目录
mkdir -p $BACKUP_PATH

# 备份当前.env文件
echo -e "${YELLOW}备份环境配置...${NC}"
cp $PROJECT_PATH/.env $PROJECT_PATH/.env.backup_$(date +%Y%m%d_%H%M%S)

# 进入项目目录
cd $PROJECT_PATH

# 保存本地更改（如果有）
echo -e "${YELLOW}保存本地更改...${NC}"
git stash

# 拉取最新代码
echo -e "${YELLOW}拉取最新代码...${NC}"
git pull origin main

if [ $? -eq 0 ]; then
    echo -e "${GREEN}代码拉取成功${NC}"
else
    echo -e "${RED}代码拉取失败，请检查网络或权限${NC}"
    exit 1
fi

# 恢复.env文件（保持服务器配置）
echo -e "${YELLOW}恢复环境配置...${NC}"
cp .env.server_backup .env

# 安装/更新Composer依赖
echo -e "${YELLOW}更新Composer依赖...${NC}"
composer install --optimize-autoloader --no-dev

# 运行数据库迁移
echo -e "${YELLOW}运行数据库迁移...${NC}"
php artisan migrate --force

# 清除缓存
echo -e "${YELLOW}清除缓存...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 重新缓存配置
echo -e "${YELLOW}重新缓存配置...${NC}"
php artisan config:cache

# 设置正确的权限
echo -e "${YELLOW}设置文件权限...${NC}"
chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 重启PHP-FPM（如果需要）
echo -e "${YELLOW}重启PHP-FPM...${NC}"
systemctl reload php-fpm 2>/dev/null || service php-fpm reload 2>/dev/null || echo "PHP-FPM重启跳过"

echo -e "${GREEN}Git部署完成！${NC}"
echo -e "${GREEN}当前版本：$(git rev-parse --short HEAD)${NC}"
echo -e "${GREEN}部署时间：$(date)${NC}" 