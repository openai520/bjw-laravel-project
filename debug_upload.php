<?php
// 临时文件上传脚本
$source_file = 'resources/views/admin/layouts/app.blade.php';
$target_path = '/home/kala/laravel-apps/kalala/resources/views/admin/layouts/app.blade.php';

// 检查文件是否存在
if (! file_exists($source_file)) {
    exit("源文件不存在: $source_file\n");
}

// 读取文件内容
$content = file_get_contents($source_file);

// 使用 SFTP 上传文件
$host = 'kalala.me';
$username = 'kala';
$port = 22;

// 使用 rsync 命令上传
$command = "rsync -avz --progress '$source_file' '$username@$host:$target_path'";
echo "执行命令: $command\n";
exec($command, $output, $return_var);

echo "返回值: $return_var\n";
echo "输出:\n";
print_r($output);

// 如果rsync失败，尝试使用scp
if ($return_var !== 0) {
    echo "\nrsync失败，尝试使用scp...\n";
    $scp_command = "scp '$source_file' '$username@$host:$target_path'";
    echo "执行命令: $scp_command\n";
    exec($scp_command, $scp_output, $scp_return);

    echo "SCP返回值: $scp_return\n";
    echo "SCP输出:\n";
    print_r($scp_output);
}

echo "\n文件上传脚本执行完成。\n";
?> 