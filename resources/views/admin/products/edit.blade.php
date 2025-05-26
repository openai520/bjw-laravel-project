@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ __('admin.edit_product') }}</h1>
        <a href="{{ route('admin.products.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            {{ __('admin.back_to_list') }}
        </a>
    </div>

    <!-- 全局提示消息 -->
    <div id="global-message" class="fixed top-4 right-4 z-50 hidden">
        <div class="max-w-sm bg-white border rounded-lg shadow-lg p-4">
            <div class="flex items-center">
                <div id="message-icon" class="flex-shrink-0 w-6 h-6 mr-3"></div>
                <div id="message-text" class="text-sm font-medium"></div>
            </div>
        </div>
    </div>

    <!-- 错误信息显示区域 -->
    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- 成功消息 -->
    <div id="success-message" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"></div>

    <form id="editProductForm"
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4"
          action="{{ route('admin.products.update', $product->id) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- 基本信息 -->
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                {{ __('admin.product_name') }}
            </label>
            <input type="text"
                   name="name"
                   id="name"
                   value="{{ old('name', $product->name) }}"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror"
                   required>
            @error('name')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="category_id">
                {{ __('admin.category') }}
            </label>
            <select name="category_id"
                    id="category_id"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('category_id') border-red-500 @enderror"
                    required>
                <option value="">{{ __('admin.select_category') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name_en }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="price">
                {{ __('admin.price') }}
            </label>
            <input type="number"
                   name="price"
                   id="price"
                   step="0.01"
                   value="{{ old('price', $product->price) }}"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('price') border-red-500 @enderror"
                   required>
            @error('price')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="min_order_quantity">
                {{ __('admin.min_order_quantity') }}
            </label>
            <input type="number"
                   name="min_order_quantity"
                   id="min_order_quantity"
                   value="{{ old('min_order_quantity', $product->min_order_quantity) }}"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('min_order_quantity') border-red-500 @enderror"
                   required>
            @error('min_order_quantity')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                {{ __('admin.description') }}
            </label>
            <p class="text-gray-600 text-sm mb-2">{{ __('admin.description_hint') }}</p>
            <textarea name="description"
                      id="description"
                      rows="5"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('description') border-red-500 @enderror"
                      required>{{ old('description', $product->description) }}</textarea>
            @error('description')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                {{ __('admin.status') }}
            </label>
            <select name="status"
                    id="status"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('status') border-red-500 @enderror">
                <option value="published" {{ old('status', $product->status) == 'published' ? 'selected' : '' }}>
                    {{ __('admin.status_published') }}
                </option>
                <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>
                    {{ __('admin.status_draft') }}
                </option>
            </select>
            @error('status')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <!-- 图片上传区域 -->
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                {{ __('admin.product_images') }}
            </label>

            <!-- 现有图片 -->
            @if($product->images->isNotEmpty())
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('admin.existing_images') }}</h3>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6" id="existing-images-container">
                        @foreach($product->images as $image)
                            <div class="relative group existing-image-item" id="image-{{ $image->id }}" data-image-id="{{ $image->id }}">
                                <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ __('admin.product_image') }}" class="h-24 w-24 object-cover rounded-lg">
                                @if($image->is_main)
                                    <span class="absolute top-0 left-0 bg-blue-500 text-white text-xs px-1 py-0.5 rounded-tl-lg rounded-br-lg main-image-badge">{{ __('admin.main_image') }}</span>
                                @else
                                    <span class="absolute top-0 left-0 bg-blue-500 text-white text-xs px-1 py-0.5 rounded-tl-lg rounded-br-lg main-image-badge hidden">{{ __('admin.main_image') }}</span>
                                @endif

                                <!-- 设置主图 Radio -->
                                <div class="absolute bottom-1 left-1" title="{{ __('admin.set_as_main') }}">
                                    <input type="radio" name="main_image_id" id="main_image_{{ $image->id }}" value="{{ $image->id }}" {{ $image->is_main ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                </div>

                                <!-- 删除按钮 -->
                                <button type="button"
                                      class="delete-image absolute top-0 right-0 p-1 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 focus:outline-none -mt-2 -mr-2"
                                      data-image-id="{{ $image->id }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 上传新图片 -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6" id="dropzone">
                <div class="text-center" id="dropzone-text">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="mt-1 text-sm text-gray-600">{{ __('admin.drag_images') }}</p>
                    <p class="text-xs text-gray-500">{{ __('admin.image_requirements') }}</p>
                    <input type="file"
                        id="imageInput"
                        name="images[]"
                        multiple
                        accept="image/*"
                        class="hidden">
                    <button type="button" id="browse-btn" class="mt-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('admin.browse_files') }}
                    </button>
                </div>

                <!-- 预览区域 -->
                <div id="image-preview" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 mt-4"></div>
            </div>

            @error('images')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
            @error('images.*')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <!-- 提交按钮 -->
        <div class="flex items-center justify-between">
            <button type="submit"
                    id="submit-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                {{ __('admin.update_product') }}
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 获取所需的DOM元素
        const form = document.getElementById('editProductForm');
        const imageInput = document.querySelector('#imageInput');
        const browseBtn = document.getElementById('browse-btn');
        const dropzone = document.getElementById('dropzone');
        const imagePreview = document.getElementById('image-preview');
        const deleteButtons = document.querySelectorAll('.delete-image');

        console.log('初始化完成，找到以下元素:', {
            form: !!form,
            imageInput: !!imageInput,
            browseBtn: !!browseBtn,
            dropzone: !!dropzone,
            imagePreview: !!imagePreview,
            deleteButtons: deleteButtons.length
        });

        // 浏览按钮点击事件 - 使用事件委托
        document.body.addEventListener('click', function(e) {
            if (e.target.id === 'browse-btn' || e.target.closest('#browse-btn')) {
                e.preventDefault();
                e.stopPropagation();
                console.log('点击浏览按钮 (通过事件委托)');
                document.getElementById('imageInput').click();
            }
        });

        // 图片输入变化处理
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                console.log('选择了文件:', this.files.length);
                handleFiles(Array.from(this.files));
            });
        }

        // 删除按钮事件 - 使用事件委托
        document.body.addEventListener('click', function(e) {
            const deleteButton = e.target.closest('.delete-image');
            if (deleteButton) {
                e.preventDefault();
                e.stopPropagation();
                const imageId = deleteButton.getAttribute('data-image-id');
                console.log('点击删除按钮 (通过事件委托)，图片ID:', imageId);

                if (confirm('确定要删除这张图片吗？')) {
                    deleteImage(imageId, deleteButton);
                }
            }
        });

        // 处理图片文件
        function handleFiles(files) {
            console.log('处理文件:', files.length);

            files.forEach(file => {
                if (!file.type.startsWith('image/')) {
                    alert('请选择图片文件');
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    alert('图片大小不能超过10MB');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('div');
                    preview.className = 'relative';
                    preview.innerHTML = `
                        <img src="${e.target.result}" class="h-16 w-16 object-cover rounded-lg" alt="预览图片">
                        <button type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    `;

                    preview.querySelector('button').addEventListener('click', () => {
                        preview.remove();
                    });

                    imagePreview.appendChild(preview);
                };
                reader.readAsDataURL(file);
            });
        }

        // 删除图片函数
        function deleteImage(imageId, button) {
            // 获取CSRF令牌 - 从meta标签获取
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            // 构建URL - 确保使用正确的路由名称
            const url = `{{ route('admin.products.images.destroy', ['product' => $product->id, 'image' => '_ID_']) }}`.replace('_ID_', imageId);
            console.log('发送删除请求:', url);
            console.log('使用CSRF令牌:', csrfToken);

            // 显示加载状态
            const originalContent = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="animate-spin inline-block">⌛</span>';

            // 使用原生XMLHttpRequest而不是fetch，以便更好地调试
            const xhr = new XMLHttpRequest();
            xhr.open('DELETE', url, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('Content-Type', 'application/json');

            xhr.onreadystatechange = function() {
                console.log('XHR状态变化:', xhr.readyState, xhr.status);

                if (xhr.readyState === 4) {
                    console.log('XHR完成，状态码:', xhr.status);
                    console.log('响应文本:', xhr.responseText);

                    try {
                        const data = JSON.parse(xhr.responseText);
                        console.log('解析的响应数据:', data);

                        if (data.success) {
                            const imageContainer = document.querySelector(`[data-image-id="${imageId}"]`).closest('.existing-image-item');
                            if (imageContainer) {
                                // 添加淡出动画
                                imageContainer.style.transition = 'opacity 0.3s ease-out';
                                imageContainer.style.opacity = '0';
                                setTimeout(() => {
                                    imageContainer.remove();
                                    showMessage('图片已删除', 'success');
                                }, 300);
                            }
                        } else {
                            button.disabled = false;
                            button.innerHTML = originalContent;
                            showMessage(data.message || '删除失败', 'error');
                        }
                    } catch (e) {
                        console.error('解析响应失败:', e);
                        button.disabled = false;
                        button.innerHTML = originalContent;
                        showMessage('删除失败，响应格式错误', 'error');
                    }
                }
            };

            xhr.onerror = function() {
                console.error('XHR错误');
                button.disabled = false;
                button.innerHTML = originalContent;
                showMessage('删除失败，网络错误', 'error');
            };

            xhr.send();
        }

        // 显示消息提示
        function showMessage(message, type = 'info') {
            const alert = document.createElement('div');
            alert.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-100 text-green-700' :
                type === 'error' ? 'bg-red-100 text-red-700' :
                'bg-blue-100 text-blue-700'
            }`;
            alert.textContent = message;

            document.body.appendChild(alert);

            // 淡入动画
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease-in';
            requestAnimationFrame(() => alert.style.opacity = '1');

            // 3秒后淡出并移除
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }

        // 拖拽上传
        if (dropzone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => {
                    dropzone.classList.add('border-blue-500', 'bg-blue-50');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => {
                    dropzone.classList.remove('border-blue-500', 'bg-blue-50');
                });
            });

            dropzone.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(Array.from(files));
            });
        }
    });
</script>
@endsection