// 产品管理页面批量操作功能
document.addEventListener('DOMContentLoaded', function() {
    // 获取批量操作相关元素
    const selectAllCheckbox = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const batchDeleteBtn = document.getElementById('batch-delete-btn');
    const batchStatusBtn = document.getElementById('batch-status-btn');
    const batchStatusDropdown = document.getElementById('batch-status-dropdown');
    const batchStatusOptions = document.querySelectorAll('.batch-status-option');
    const batchActionForm = document.getElementById('batch-action-form');
    const batchActionMethodInput = document.getElementById('batch-action-method');
    
    // 价格内联编辑相关元素
    const priceDisplays = document.querySelectorAll('.price-display');
    const priceEditForms = document.querySelectorAll('.price-edit-form');
    const priceCancelButtons = document.querySelectorAll('.price-cancel');
    
    // 如果页面上没有这些元素，则退出
    if (!batchDeleteBtn || !batchStatusBtn || !batchActionForm) {
        console.warn('批量操作元素未找到，批量操作功能未初始化');
        return;
    }
    
    // 处理全选/取消全选
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateBatchButtonsState();
        });
    }
    
    // 处理单个产品选中状态变化
    if (productCheckboxes) {
        productCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBatchButtonsState();
                // 如果有一个没选中，则取消全选框的选中状态
                if (!this.checked && selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                }
                // 如果所有都选中，则选中全选框
                if (selectAllCheckbox) {
                    const allChecked = Array.from(productCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                }
            });
        });
    }
    
    // 更新批量操作按钮状态
    function updateBatchButtonsState() {
        const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
        
        if (batchDeleteBtn) {
            if (checkedCount > 0) {
                batchDeleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                batchDeleteBtn.classList.add('hover:bg-red-600');
                batchDeleteBtn.disabled = false;
                // 更新按钮文字，显示选中数量
                batchDeleteBtn.textContent = `批量删除 (${checkedCount})`;
            } else {
                batchDeleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
                batchDeleteBtn.classList.remove('hover:bg-red-600');
                batchDeleteBtn.disabled = true;
                batchDeleteBtn.textContent = '批量删除';
            }
        }
        
        if (batchStatusBtn) {
            if (checkedCount > 0) {
                batchStatusBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                batchStatusBtn.disabled = false;
                // 更新按钮文字，显示选中数量
                batchStatusBtn.textContent = `批量修改状态 (${checkedCount})`;
            } else {
                batchStatusBtn.classList.add('opacity-50', 'cursor-not-allowed');
                batchStatusBtn.disabled = true;
                batchStatusBtn.textContent = '批量修改状态';
            }
        }
    }
    
    // 批量删除按钮点击事件
    if (batchDeleteBtn) {
        batchDeleteBtn.addEventListener('click', function() {
            if (this.disabled) return;
            
            const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
            if (checkedBoxes.length === 0) return;
            
            // 确认删除
            if (!confirm(`确定要删除选中的 ${checkedBoxes.length} 个产品吗？此操作不可恢复。`)) {
                return;
            }
            
            // 获取选中的产品ID
            const productIds = Array.from(checkedBoxes).map(cb => cb.value);
            
            // 设置表单动作和方法
            batchActionForm.action = '/admin/products/batch-destroy';
            batchActionMethodInput.value = 'DELETE';
            
            // 清空之前的隐藏字段
            const existingIdFields = batchActionForm.querySelectorAll('input[name="ids[]"]');
            existingIdFields.forEach(field => field.remove());
            
            // 添加产品ID到表单
            productIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                batchActionForm.appendChild(input);
            });
            
            // 提交表单
            batchActionForm.submit();
        });
    }
    
    // 批量修改状态选项点击事件
    if (batchStatusOptions) {
        batchStatusOptions.forEach(option => {
            option.addEventListener('click', function() {
                const status = this.dataset.status;
                const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
                if (checkedBoxes.length === 0) return;
                
                // 获取选中的产品ID，不再使用确认弹窗
                const productIds = Array.from(checkedBoxes).map(cb => cb.value);
                
                // 使用AJAX发送请求，避免页面跳转
                const formData = new FormData();
                formData.append('_method', 'PATCH');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('status', status);
                
                productIds.forEach(id => {
                    formData.append('ids[]', id);
                });
                
                // 显示加载中提示
                const loadingMessage = document.createElement('div');
                loadingMessage.className = 'fixed top-0 left-0 right-0 bg-blue-500 text-white p-2 text-center z-50';
                loadingMessage.textContent = '正在处理，请稍候...';
                document.body.appendChild(loadingMessage);
                
                // 添加请求头，明确告知服务器这是AJAX请求
                fetch('/admin/products/batch-update-status', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // 移除加载中提示
                    loadingMessage.remove();
                    
                    // 创建成功提示
                    const successMessage = document.createElement('div');
                    successMessage.className = 'fixed top-0 left-0 right-0 bg-green-500 text-white p-2 text-center z-50';
                    successMessage.textContent = data.message || '状态更新成功';
                    document.body.appendChild(successMessage);
                    
                    // 3秒后自动移除提示
                    setTimeout(() => {
                        successMessage.remove();
                        // 刷新页面以更新状态显示
                        window.location.reload();
                    }, 2000);
                })
                .catch(error => {
                    // 移除加载中提示
                    loadingMessage.remove();
                    
                    // 创建错误提示
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'fixed top-0 left-0 right-0 bg-red-500 text-white p-2 text-center z-50';
                    errorMessage.textContent = '操作失败，请重试';
                    document.body.appendChild(errorMessage);
                    
                    // 3秒后自动移除提示
                    setTimeout(() => {
                        errorMessage.remove();
                    }, 3000);
                    
                    console.error('Error:', error);
                });
            });
        });
    }
    
    // 价格内联编辑功能
    if (priceDisplays.length > 0) {
        // 点击价格显示，切换到编辑模式
        priceDisplays.forEach(display => {
            display.addEventListener('click', function() {
                const productId = this.dataset.id;
                const editForm = document.querySelector(`.price-edit-form[data-id="${productId}"]`);
                
                // 隐藏显示元素，显示编辑表单
                this.classList.add('hidden');
                editForm.classList.remove('hidden');
                
                // 聚焦到输入框
                const input = editForm.querySelector('input[name="price"]');
                input.focus();
                input.select();
            });
        });
        
        // 取消编辑
        priceCancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.price-edit-form');
                const productId = form.dataset.id;
                const display = document.querySelector(`.price-display[data-id="${productId}"]`);
                
                // 隐藏编辑表单，显示价格
                form.classList.add('hidden');
                display.classList.remove('hidden');
            });
        });
        
        // 提交价格更新
        priceEditForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const productId = this.dataset.id;
                const priceInput = this.querySelector('input[name="price"]');
                const newPrice = parseFloat(priceInput.value);
                const display = document.querySelector(`.price-display[data-id="${productId}"]`);
                
                if (isNaN(newPrice) || newPrice < 0) {
                    alert('请输入有效的价格');
                    priceInput.focus();
                    return;
                }
                
                // 创建FormData对象
                const formData = new FormData();
                formData.append('_method', 'PATCH');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('price', newPrice);
                
                // 显示加载提示
                display.textContent = '更新中...';
                this.classList.add('hidden');
                display.classList.remove('hidden');
                
                // 发送AJAX请求
                fetch(`/admin/products/${productId}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('更新失败');
                    }
                    return response.json();
                })
                .then(data => {
                    // 更新显示的价格
                    display.textContent = '$' + newPrice.toFixed(2);
                    display.dataset.originalPrice = newPrice;
                    
                    // 显示成功提示
                    const successMessage = document.createElement('div');
                    successMessage.className = 'fixed top-0 left-0 right-0 bg-green-500 text-white p-2 text-center z-50';
                    successMessage.textContent = '价格更新成功';
                    document.body.appendChild(successMessage);
                    
                    // 3秒后移除提示
                    setTimeout(() => {
                        successMessage.remove();
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // 还原原始价格
                    const originalPrice = parseFloat(display.dataset.originalPrice);
                    display.textContent = '$' + originalPrice.toFixed(2);
                    
                    // 显示错误提示
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'fixed top-0 left-0 right-0 bg-red-500 text-white p-2 text-center z-50';
                    errorMessage.textContent = '价格更新失败，请重试';
                    document.body.appendChild(errorMessage);
                    
                    // 3秒后移除提示
                    setTimeout(() => {
                        errorMessage.remove();
                    }, 3000);
                });
            });
        });
    }
    
    // 初始化状态
    updateBatchButtonsState();
}); 