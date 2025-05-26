# 常见问题解答 (FAQ)

## 环境配置问题

### Q: composer install 失败怎么办？
**A:** 
1. 检查PHP版本是否 >= 8.1
2. 确保安装了必需的PHP扩展：
   ```bash
   php -m | grep -E "(openssl|pdo|mbstring|tokenizer|xml|ctype|json|bcmath|fileinfo)"
   ```
3. 尝试清除composer缓存：
   ```bash
   composer clear-cache
   composer install
   ```

### Q: npm install 报错如何解决？
**A:**
1. 检查Node.js版本： `node --version` (需要 >= 16.x)
2. 清除npm缓存：
   ```bash
   npm cache clean --force
   rm -rf node_modules package-lock.json
   npm install
   ```
3. 如果还有问题，尝试使用yarn：
   ```bash
   yarn install
   ```

### Q: 数据库连接失败
**A:**
1. 检查 `.env` 文件中的数据库配置
2. 确认MySQL服务运行：`brew services start mysql` (macOS)
3. 测试数据库连接：
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

## 功能使用问题

### Q: 购物车商品数量无法修改
**A:**
1. 检查JavaScript控制台是否有错误
2. 确认 `validateQuantity()` 函数已正确加载
3. 检查CSRF令牌是否正确设置

### Q: 产品图片不显示
**A:**
1. 检查存储链接：`php artisan storage:link`
2. 验证图片文件是否存在于 `public/storage/products/` 目录
3. 检查文件权限：`chmod -R 755 public/storage`
4. 查看Product模型的 `getMainImageUrlAttribute()` 方法

### Q: 多语言切换不生效
**A:**
1. 检查语言文件是否存在于 `resources/lang/` 目录
2. 清除视图缓存：`php artisan view:clear`
3. 检查路由是否包含语言参数
4. 验证 `app()->setLocale()` 是否正确调用

### Q: 购物车页面布局混乱
**A:**
1. 检查Tailwind CSS是否正确编译：`npm run dev`
2. 清除浏览器缓存
3. 检查响应式类是否正确应用（sm:, md:, lg:）
4. 验证flexbox和grid布局是否冲突

## 部署问题

### Q: 生产环境500错误
**A:**
1. 检查错误日志：`tail -f storage/logs/laravel.log`
2. 确认 `.env` 文件配置正确
3. 检查文件权限：
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```
4. 运行优化命令：
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Q: 上传图片失败
**A:**
1. 检查上传目录权限：`chmod -R 775 public/storage/products`
2. 验证PHP上传限制：
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 300
   ```
3. 检查存储磁盘配置

### Q: 静态资源404错误
**A:**
1. 运行前端构建：`npm run build`
2. 检查Nginx/Apache配置是否正确
3. 验证public目录权限
4. 确认资源路径是否正确

## 性能问题

### Q: 页面加载速度慢
**A:**
1. 开启数据库查询缓存
2. 使用Redis进行会话和缓存存储
3. 优化图片大小和格式（WebP）
4. 启用HTTP/2和Gzip压缩
5. 使用CDN加速静态资源

### Q: 数据库查询性能差
**A:**
1. 添加数据库索引
2. 使用查询构建器而非原生SQL
3. 实现查询缓存
4. 优化N+1查询问题：
   ```php
   Product::with(['category', 'images'])->get();
   ```

### Q: 内存占用过高
**A:**
1. 优化代码中的内存使用
2. 使用分页而非一次性加载大量数据
3. 定期清理日志文件
4. 调整PHP内存限制

## 安全问题

### Q: 如何防止SQL注入？
**A:**
1. 始终使用查询构建器或预处理语句
2. 验证和过滤用户输入
3. 使用Laravel的验证机制
4. 定期更新依赖包

### Q: 如何处理文件上传安全？
**A:**
1. 验证文件类型和大小
2. 使用白名单而非黑名单
3. 重命名上传文件
4. 存储文件到非公开目录
5. 扫描恶意代码

### Q: 防止CSRF攻击
**A:**
1. 在表单中包含CSRF令牌：`@csrf`
2. Ajax请求中包含令牌：
   ```javascript
   headers: {
       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
   }
   ```
3. 验证来源引用

## 开发技巧

### Q: 如何调试Laravel应用？
**A:**
1. 使用 `dd()` 和 `dump()` 函数
2. 启用查询日志：
   ```php
   DB::enableQueryLog();
   // 执行查询
   dd(DB::getQueryLog());
   ```
3. 使用Laravel Telescope（开发环境）
4. 查看日志文件：`tail -f storage/logs/laravel.log`

### Q: 如何优化开发工作流？
**A:**
1. 使用 `php artisan serve` 快速启动开发服务器
2. 配置文件监听：`npm run watch`
3. 使用Artisan命令快速生成代码
4. 设置IDE代码提示和自动补全
5. 使用Git钩子自动化测试

### Q: 如何处理大数据量？
**A:**
1. 使用数据库分页：`paginate()`
2. 实现懒加载：`lazy()`
3. 使用队列处理耗时任务
4. 分批处理数据：`chunk()`
5. 使用索引优化查询

## 联系支持
如果以上FAQ无法解决您的问题，请：
1. 查看项目文档
2. 搜索已有的Issue
3. 创建新的Issue并提供详细信息
4. 联系开发团队

