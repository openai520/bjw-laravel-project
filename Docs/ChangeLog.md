# Changelog
所有对此项目的重要更改都将记录在此文件中。

本文档格式基于 [Keep a Changelog](https://keepachangelog.com/zh-CN/1.0.0/)，
并且此项目遵循 [语义化版本](https://semver.org/lang/zh-CN/)。

## [Unreleased]
### Added
- 项目文档管理规范和模板
- 购物车响应式布局优化
- 数量控制器改进（支持5位数输入）

### Changed
- 购物车页面布局改为响应式设计
- 产品详情页数量控制器可编辑
- 单价颜色从红色改为黑色

### Fixed
- 购物车空白提示语言本地化问题
- 主页"查看更多"文本本地化
- 表单消息字段显示翻译键问题

## [1.0.0] - 2024-12-XX
### Added
- Laravel 电商基础功能
- 产品展示和管理
- 购物车功能
- 多语言支持（中文、英文、法文）
- 管理后台
- 用户认证系统
- 产品分类管理
- 图片上传和管理
- 响应式前端设计

### Features
- **产品管理**
  - 产品CRUD操作
  - 图片上传和展示
  - 分类管理
  - 价格管理
  
- **购物车**
  - 添加/删除商品
  - 数量调整
  - 小计计算
  - 响应式布局

- **多语言**
  - 中文简体
  - 英文
  - 法文
  - 动态语言切换

- **管理后台**
  - 产品管理
  - 分类管理
  - 用户管理
  - 系统设置

### Technical
- Laravel 10.x 框架
- Tailwind CSS 样式
- MySQL 数据库
- Vite 前端构建
- Laravel Sanctum 认证
- 图片处理和优化

### Deployment
- Nginx 服务器配置
- 生产环境优化
- 自动部署脚本
- 缓存配置

