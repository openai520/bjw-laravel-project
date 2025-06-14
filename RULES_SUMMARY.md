# 项目规则摘要（2025年6月）

- 本项目为Laravel 10.x + MariaDB 10.11.4的B2B电商平台，支持多语言，主域名https://kalala.me
- 所有产品图片（主图/缩略图）已统一为WebP格式，数据库和文件系统路径均已批量修正
- 前端UI全面适配移动端，模态框、卡片、无限滚动等均已优化
- 本地开发直连云数据库，依赖Composer+NPM，权限/缓存/日志严格规范
- 自动化部署推荐deploy.sh/quick-deploy.sh，支持Git一键回滚、备份、权限自动设置
- 生产环境敏感信息仅存.env，所有表单/接口均含CSRF/XSS防护，后台路由auth+admin保护
- 重大变更、历史回退、图片批量转换、数据库修复等全部纳入本规则
- AI Agent Protocol强制终审，所有AI任务须经final_review_gate.py交互确认
- 本文件与PROJECT_RULES_UPDATED_2025.mdx为唯一权威规则入口，所有说明文档须定期同步 