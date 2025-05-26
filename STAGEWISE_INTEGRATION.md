# Stagewise 工具栏集成说明

## 概述

项目已成功集成 Stagewise 开发工具栏，这是一个浏览器工具栏，可以将前端UI连接到代码编辑器中的AI代理，允许开发者在web应用中选择元素、留下评论，并让AI代理基于这些上下文进行代码更改。

## 安装的包

- `@stagewise/toolbar` - 框架无关的stagewise工具栏核心包

## 集成位置

### 主要文件
- `resources/js/app.js` - 添加了stagewise工具栏的初始化代码
- `vite.config.js` - 更新了构建配置以确保生产环境不包含stagewise

### 代码更改

#### 1. JavaScript入口文件 (resources/js/app.js)
```javascript
// Stagewise 工具栏集成 - 只在开发环境中运行
if (import.meta.env.DEV) {
    import('@stagewise/toolbar').then(({ initToolbar }) => {
        // Stagewise 工具栏配置
        const stagewiseConfig = {
            plugins: []
        };
        
        // 等待 DOM 加载完成后初始化工具栏
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                initToolbar(stagewiseConfig);
            });
        } else {
            initToolbar(stagewiseConfig);
        }
    }).catch(err => {
        console.warn('Stagewise toolbar 加载失败:', err);
    });
}
```

#### 2. Vite配置 (vite.config.js)
```javascript
build: {
    rollupOptions: {
        external: (id) => {
            // 在生产构建中排除 stagewise 相关包
            if (process.env.NODE_ENV === 'production' && id.includes('@stagewise/')) {
                return true;
            }
            return false;
        }
    },
}
```

## 使用方法

### 开发环境
1. 启动开发服务器：
   ```bash
   npm run dev
   ```

2. 打开浏览器访问你的Laravel应用

3. Stagewise工具栏将自动出现在页面上（仅在开发环境）

### 生产环境
- Stagewise工具栏不会在生产环境中加载
- 生产构建中不会包含stagewise相关代码

## 功能特性

- ✅ 仅在开发环境中运行
- ✅ 不影响生产构建
- ✅ 与Alpine.js、FilePond等现有组件兼容
- ✅ 异步加载，不阻塞主应用启动
- ✅ 错误处理，加载失败时不影响应用运行

## 配置自定义

可以在 `resources/js/app.js` 中的 `stagewiseConfig` 对象中添加自定义插件和配置：

```javascript
const stagewiseConfig = {
    plugins: [
        // 在这里添加自定义插件
    ],
    // 其他配置选项
};
```

## 故障排除

如果工具栏没有出现：

1. 确保正在开发环境中运行
2. 检查浏览器开发者工具的控制台是否有错误
3. 确认Vite开发服务器正在运行
4. 验证网络连接，确保可以下载stagewise包

## 验证集成

可以运行以下命令验证集成是否正确：

```bash
# 开发环境测试
npm run dev

# 生产构建测试
npm run build
```

两个命令都应该成功执行且无错误。 