@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- 标题和创建按钮 -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">产品管理</h1>
            <div class="flex space-x-2">
                <a href="{{ route('admin.products.batch_upload.form', ['page' => request()->query('page', 1)]) }}" class="btn bg-green-600 hover:bg-green-700 text-white">
                    批量添加产品
                </a>
                <a href="{{ route('admin.products.create', ['page' => request()->query('page', 1)]) }}" class="btn btn-primary">
                    创建产品
                </a>
            </div>
        </div>

        <!-- 搜索表单 -->
        <div class="mb-4">
            <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-wrap items-center">
                <div class="relative flex-grow mr-2 mb-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search_by_product_name') }}"
                           class="w-full h-10 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <!-- 分类筛选 -->
                <div class="relative mr-2 mb-2">
                    <select name="category_id" class="h-10 pl-3 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">全部分类</option>
                        @foreach(\App\Models\Category::orderBy('name_en')->get() as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- 状态筛选 -->
                <div class="relative mr-2 mb-2">
                    <select name="status" class="h-10 pl-3 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">全部状态</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>已发布</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>草稿</option>
                    </select>
                </div>
                
                <!-- 价格筛选 -->
                <div class="flex items-center mr-2 mb-2">
                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="最低价" min="0" step="0.01"
                           class="w-24 h-10 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm mr-1">
                    <span class="mx-1">-</span>
                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="最高价" min="0" step="0.01"
                           class="w-24 h-10 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <div class="flex space-x-2 mb-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:shadow-outline">
                        <span class="flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            筛选
                        </span>
                    </button>
                    @if(request('search') || request('category_id') || request('status') || request('min_price') || request('max_price'))
                        <a href="{{ route('admin.products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md focus:outline-none focus:shadow-outline">
                            重置
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- 批量操作按钮 -->
        <div class="flex space-x-2 mb-4">
            <button id="batch-delete-btn" class="btn btn-danger opacity-50 cursor-not-allowed" disabled>批量删除</button>
            
            <div class="relative" x-data="{ open: false }">
                <button id="batch-status-btn" @click="open = !open" class="btn btn-secondary opacity-50 cursor-not-allowed" disabled>批量修改状态</button>
                <div id="batch-status-dropdown" x-show="open" @click.away="open = false" class="absolute z-10 mt-1 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100">
                    <div class="py-1">
                        <button class="batch-status-option block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" data-status="published">设为已发布</button>
                        <button class="batch-status-option block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" data-status="draft">设为草稿</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash 消息 -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- 批量操作表单 -->
        <form id="batch-action-form" method="POST" style="display: none;">
            @csrf
            <input type="hidden" id="batch-action-method" name="_method" value="">
        </form>

        <!-- 产品列表表格 -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">主图</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">名称</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">分类</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">价格</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">访问次数</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-40">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($products as $product)
                        <tr>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <input type="checkbox" class="product-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ $product->id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $mainImage = $product->images->where('is_main', true)->first();
                                    $imageUrl = $mainImage && $mainImage->image_path ? Storage::url($mainImage->image_path) : asset('img/placeholder.png');
                                @endphp
                                <img src="{{ $imageUrl }}"
                                     alt="{{ $product->name ?? 'Product Image' }}"
                                     class="w-16 h-16 object-cover rounded">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name ?? $product->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $product->category->name_en ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="price-container" data-id="{{ $product->id }}">
                                    <div class="price-display cursor-pointer" onclick="editPrice({{ $product->id }})">
                                        ${{ number_format($product->price, 2) }}
                                    </div>
                                    <div class="price-edit hidden">
                                        <input type="number" step="0.01" min="0" class="price-input form-input w-20 rounded border-gray-300 shadow-sm" value="{{ $product->price }}" onkeypress="return priceKeyPress(event, {{ $product->id }})">
                                        <button type="button" class="ml-1 text-xs text-blue-600" onclick="updatePrice({{ $product->id }})">✓</button>
                                        <button type="button" class="ml-1 text-xs text-red-600" onclick="cancelEditPrice({{ $product->id }})">✗</button>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-blue-600">{{ $product->view_count ?? 0 }}</div>
                                    <div class="text-xs text-gray-500">今日: {{ $product->today_view_count ?? 0 }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $product->status === 'published' ? '已发布' : '草稿' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.products.edit', ['product' => $product, 'page' => request()->query('page', 1)]) }}" class="btn btn-secondary px-2 py-1">编辑</a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline"
                                    onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="page" value="{{ request()->query('page', 1) }}">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        {{ __('admin.delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 分页链接 -->
        <div class="mt-4">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// 内联价格编辑功能
function editPrice(productId) {
    const container = document.querySelector(`.price-container[data-id="${productId}"]`);
    const display = container.querySelector('.price-display');
    const edit = container.querySelector('.price-edit');
    const input = container.querySelector('.price-input');
    
    // 隐藏显示元素，显示编辑区域
    display.classList.add('hidden');
    edit.classList.remove('hidden');
    
    // 聚焦到输入框
    input.focus();
    input.select();
}

function cancelEditPrice(productId) {
    const container = document.querySelector(`.price-container[data-id="${productId}"]`);
    const display = container.querySelector('.price-display');
    const edit = container.querySelector('.price-edit');
    
    // 隐藏编辑区域，显示价格显示
    edit.classList.add('hidden');
    display.classList.remove('hidden');
}

function priceKeyPress(event, productId) {
    // 按回车键时更新价格
    if (event.key === 'Enter') {
        updatePrice(productId);
        return false;
    }
    return true;
}

function updatePrice(productId) {
    const container = document.querySelector(`.price-container[data-id="${productId}"]`);
    const display = container.querySelector('.price-display');
    const edit = container.querySelector('.price-edit');
    const input = container.querySelector('.price-input');
    const originalPriceText = display.textContent; // 保存原始价格文本
    const newPrice = parseFloat(input.value);

    if (isNaN(newPrice) || newPrice < 0) {
        alert('请输入有效的价格');
        input.focus();
        return;
    }

    // Optimistically update UI, but be ready to revert
    display.textContent = '更新中...';
    edit.classList.add('hidden');
    display.classList.remove('hidden');

    const formData = new FormData();
    formData.append('_method', 'PATCH');
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('price', newPrice);

    fetch(`/admin/products/${productId}/update-price`, {
        method: 'POST', // Laravel handles PATCH via _method field in FormData with POST
        body: formData,
        headers: { // 添加 Accept header 确保服务器知道我们期望 JSON
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            // 如果 HTTP 状态码不是 2xx, 尝试读取错误信息
            return response.json().then(errData => {
                throw { status: response.status, data: errData };
            }).catch(() => {
                // 如果无法解析 JSON，则抛出通用错误
                throw { status: response.status, data: { message: '服务器响应错误' } };
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && typeof data.price !== 'undefined') {
            display.textContent = '$' + parseFloat(data.price).toFixed(2);
            // 可选：短暂显示成功提示
            // input.value = parseFloat(data.price).toFixed(2); // 更新输入框的值以保持同步
        } else {
            alert(data.message || '更新失败，请重试');
            display.textContent = originalPriceText; // 恢复原始价格
        }
    })
    .catch(error => {
        console.error('Error updating price:', error);
        let alertMessage = '更新价格时发生网络错误或服务器错误。';
        if (error && error.data && error.data.message) {
            alertMessage = error.data.message;
        } else if (error && error.message) {
            alertMessage = error.message;
        }
        alert(alertMessage);
        display.textContent = originalPriceText; // 发生错误时恢复原始价格
    })
    .finally(() => {
        // 确保编辑框在操作完成后总是隐藏，显示框总是可见
        edit.classList.add('hidden');
        display.classList.remove('hidden');
    });
}

// 批量操作功能
document.addEventListener('DOMContentLoaded', function() {
    // 获取批量操作相关元素
    const selectAllCheckbox = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const batchDeleteBtn = document.getElementById('batch-delete-btn');
    const batchStatusBtn = document.getElementById('batch-status-btn');
    const batchStatusOptions = document.querySelectorAll('.batch-status-option');
    const batchActionForm = document.getElementById('batch-action-form');
    
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
                batchDeleteBtn.disabled = false;
                // 更新按钮文字，显示选中数量
                batchDeleteBtn.textContent = `批量删除 (${checkedCount})`;
            } else {
                batchDeleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
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
    
    // 批量状态更新处理
    if (batchStatusOptions) {
        batchStatusOptions.forEach(option => {
            option.addEventListener('click', function() {
                const status = this.dataset.status;
                const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
                if (checkedBoxes.length === 0) return;
                
                // 获取选中的产品ID
                const productIds = Array.from(checkedBoxes).map(cb => cb.value);
                
                // 创建表单数据
                const formData = new FormData();
                formData.append('_method', 'PATCH');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('status', status);
                productIds.forEach(id => {
                    formData.append('ids[]', id);
                });
                
                // 显示处理中状态
                showMessage('处理中...', 'info');
                
                // 发送AJAX请求，明确设置请求头
                fetch('{{ route('admin.products.batch-update-status') }}', { // 使用 Blade 渲染路由
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // FormData 会自动处理 CSRF
                    },
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        // 尝试解析 JSON 错误信息
                        return response.json().then(err => { throw err; }).catch(() => { throw new Error('Request failed with status ' + response.status); });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showMessage('状态更新成功：' + (data.message || '操作完成'), 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showMessage('更新失败：' + (data.message || '未知错误'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    let errorMessage = '操作失败';
                    if (error && error.message) {
                        errorMessage += '：' + error.message;
                    } else if (typeof error === 'object' && error.errors) {
                        errorMessage += '：' + Object.values(error.errors).flat().join(' ');
                    }
                    showMessage(errorMessage, 'error');
                });
            });
        });
    }

    // 批量删除处理
    if (batchDeleteBtn) {
        batchDeleteBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
            if (checkedBoxes.length === 0) {
                showMessage('请先选择要删除的产品。', 'warning');
                return;
            }

            if (!confirm(`确定要删除选中的 ${checkedBoxes.length} 个产品吗？此操作不可撤销。`)) {
                return;
            }

            const productIds = Array.from(checkedBoxes).map(cb => cb.value);

            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            productIds.forEach(id => {
                formData.append('ids[]', id);
            });

            showMessage('正在删除...', 'info');

            fetch('{{ route('admin.products.batch-destroy') }}', {
                method: 'POST', // HTML 表单不支持 DELETE，通过 _method 伪造
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; }).catch(() => { throw new Error('Request failed with status ' + response.status); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showMessage('产品删除成功：' + (data.message || `${data.deleted_count || '部分'}个产品已删除。`), 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage('删除失败：' + (data.message || '未知错误'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                let errorMessage = '操作失败';
                if (error && error.message) {
                    errorMessage += '：' + error.message;
                } else if (typeof error === 'object' && error.errors) {
                    errorMessage += '：' + Object.values(error.errors).flat().join(' ');
                }
                showMessage(errorMessage, 'error');
            });
        });
    }
});

// 辅助函数：显示消息提示
function showMessage(message, type = 'info') {
    // 移除任何现有消息
    const existingMessages = document.querySelectorAll('.message-alert');
    existingMessages.forEach(msg => msg.remove());
    
    // 创建新消息元素
    const messageElement = document.createElement('div');
    messageElement.className = `message-alert fixed top-0 left-0 right-0 p-3 text-white text-center z-50`;
    
    // 根据类型设置背景色
    switch (type) {
        case 'success':
            messageElement.classList.add('bg-green-500');
            break;
        case 'error':
            messageElement.classList.add('bg-red-500');
            break;
        case 'warning':
            messageElement.classList.add('bg-yellow-500');
            break;
        default:
            messageElement.classList.add('bg-blue-500');
    }
    
    messageElement.textContent = message;
    document.body.appendChild(messageElement);
    
    // 3秒后自动移除
    setTimeout(() => {
        messageElement.remove();
    }, 3000);
}
</script>
@endpush