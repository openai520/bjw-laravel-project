import './bootstrap';

// 导入 FilePond CSS
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';

// 导入 FilePond JS 和插件
import * as FilePond from 'filepond';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';

// 注册 FilePond 插件
FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize
);

// 将 FilePond 赋值给 window 对象，使其在 Blade 视图中的 <script> 标签内可用
window.FilePond = FilePond;

// 注册 Alpine.js 插件
import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

// 注册 Alpine.js 插件
window.Alpine = Alpine;
Alpine.plugin(intersect);
Alpine.start();

// 全局 Toast 提示函数
window.showToast = function(message, type = 'success') {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-md shadow-lg text-white text-sm z-50 transition-all duration-300 transform translate-y-full opacity-0`;

    if (type === 'success') {
        toast.classList.add('bg-green-500');
    } else {
        toast.classList.add('bg-red-500');
    }

    document.body.appendChild(toast);

    // 淡入动画
    setTimeout(() => {
        toast.classList.remove('translate-y-full', 'opacity-0');
    }, 100);

    // 淡出动画并移除
    setTimeout(() => {
        toast.classList.add('translate-y-full', 'opacity-0');
        toast.addEventListener('transitionend', () => {
            toast.remove();
        });
    }, 3000);
}

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
