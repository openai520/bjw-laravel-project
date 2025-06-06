<!DOCTYPE html>
<html>
<head>
    <title>简单测试</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { background: green; color: white; padding: 10px; }
    </style>
</head>
<body>
    <h1>简单测试页面</h1>
    <div class="success">✅ 如果您能看到这条消息，说明PHP和视图渲染正常工作</div>
    <p>时间: {{ date('Y-m-d H:i:s') }}</p>
    <p>Laravel版本: {{ app()->version() }}</p>
</body>
</html> 