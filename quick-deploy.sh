#!/bin/bash

# 快速部署脚本 - 用于频繁的小更新
# 使用方法: ./quick-deploy.sh "提交信息"

# 颜色定义
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

# 服务器配置
SERVER_HOST="root@43.165.63.73"
SERVER_PATH="/www/wwwroot/kalala.me"
SSH_KEY="~/Desktop/sursor.pem"

# 获取提交信息
COMMIT_MESSAGE="${1:-快速更新 - $(date '+%Y-%m-%d %H:%M:%S')}"

echo -e "${BLUE}🚀 快速部署开始...${NC}"
echo -e "${YELLOW}提交信息: $COMMIT_MESSAGE${NC}"

# Git 三连击
echo -e "${BLUE}📝 Git操作...${NC}"
git add . && \
git commit -m "$COMMIT_MESSAGE" && \
git push origin main

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Git推送成功${NC}"
else
    echo -e "❌ Git操作失败"
    exit 1
fi

# 上传关键文件
echo -e "${BLUE}📤 上传文件...${NC}"
scp -i $SSH_KEY -q -r app/ resources/views/ $SERVER_HOST:$SERVER_PATH/

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ 文件上传成功${NC}"
else
    echo -e "❌ 文件上传失败"
    exit 1
fi

# 清除缓存
echo -e "${BLUE}🧹 清除缓存...${NC}"
ssh -i $SSH_KEY $SERVER_HOST "cd $SERVER_PATH && php artisan cache:clear && php artisan view:clear" > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ 缓存清除成功${NC}"
else
    echo -e "❌ 缓存清除失败"
fi

echo -e "${GREEN}🎉 快速部署完成！${NC}"
echo -e "${BLUE}🌐 网站: https://kalala.me${NC}" 