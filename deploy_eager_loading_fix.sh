#!/bin/bash

# 确保脚本出错时立即退出
set -e

echo "🚀 开始部署预加载逻辑修复..."

# 定义要上传的文件数组
FILES_TO_UPLOAD=(
  "app/Http/Controllers/Frontend/HomeController.php"
  "app/Http/Controllers/Frontend/ProductController.php"
)

# 循环上传文件
for FILE in "${FILES_TO_UPLOAD[@]}"; do
  echo "📤 上传 $FILE..."
  rsync -avz "$FILE" "root@kalala.me:/var/www/laravel-site/$FILE"
done

echo "✅ 所有文件上传成功。"

echo "🧹 清理服务器缓存..."
ssh root@kalala.me "
  cd /var/www/laravel-site && \
  php artisan cache:clear && \
  php artisan config:clear && \
  php artisan view:clear && \
  php artisan route:clear && \
  echo '✅ 缓存已清理完毕。'
"

echo "🎉 部署完成！"
echo "🔍 现在，主页和产品列表页的图片应该可以正常显示了，即使它们没有被明确设置为主图。" 