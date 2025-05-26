/**
 * 产品列表布局
 * 使用CSS Grid实现响应式布局：
 * - 大屏幕(>1400px): 4列
 * - 中屏幕(1024px-1400px): 3列
 * - 小屏幕及以下(<1024px): 2列
 */

document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.masonry-container');
    if (!container) {
        console.log('Product container not found');
        return;
    }

    // 初始化时预加载图片
    imagesLoaded(container, function() {
        console.log('All images loaded initially');
    });

    // 监听新产品添加事件
    document.addEventListener('newProductsAddedFromInfiniteScroll', (event) => {
        const newElements = event.detail.newElements;
        if (newElements && newElements.length > 0) {
            imagesLoaded(newElements, function() {
                console.log('New product images loaded');
            });
        }
    });
});
