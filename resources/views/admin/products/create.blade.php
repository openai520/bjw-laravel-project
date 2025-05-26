@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">{{ __('admin.create_product') }}</h1>
        <a href="{{ route('admin.products.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
            {{ __('admin.back_to_list') }}
        </a>
    </div>

    <form id="createProductForm" 
          x-data="productFormData()"
          x-on:submit.prevent="submitForm($event)"
          class="bg-white shadow-md rounded px-6 pt-4 pb-6" 
          action="{{ route('admin.products.store') }}" 
          method="POST">
        @csrf
        
        <!-- 表单内容区域 - 2列布局 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- 左侧列 - 基本信息 -->
            <div>
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('admin.product_name') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="name" 
                               name="name" 
                               x-model="formData.name"
                               x-on:input="checkProductName($event.target.value)"
                               class="form-input w-full rounded-md shadow-sm" 
                               required>
                        <div x-show="nameError" 
                             x-text="nameError"
                             class="absolute top-full left-0 text-red-500 text-sm mt-1"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700 text-sm font-bold mb-1" for="category_id">
                        {{ __('admin.category') }}
                    </label>
                    @php
                       if (isset($categories)) {
                           echo "<!-- Categories count: " . $categories->count() . " -->";
                           foreach ($categories as $cat) {
                               echo "<!-- Category: " . $cat->id . " - " . $cat->name_en . " -->";
                           }
                       } else {
                           echo "<!-- Categories variable not set -->";
                       }
                    @endphp
                    <select name="category_id" 
                            id="category_id" 
                            class="shadow appearance-none border rounded w-full py-1.5 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{'border-red-500': errors.category_id}"
                            required>
                        <option value="">{{ __('admin.select_category') }}</option>
                        @isset($categories)
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name_en }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                    <template x-if="errors.category_id">
                        <p class="text-red-500 text-xs italic" x-text="errors.category_id[0]"></p>
                    </template>
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700 text-sm font-bold mb-1" for="price">
                        {{ __('admin.price') }}
                    </label>
                    <input type="number" 
                           name="price" 
                           id="price" 
                           step="0.01" 
                           value="{{ old('price') }}"
                           class="shadow appearance-none border rounded w-full py-1.5 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           :class="{'border-red-500': errors.price}"
                           required>
                    <template x-if="errors.price">
                        <p class="text-red-500 text-xs italic" x-text="errors.price[0]"></p>
                    </template>
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700 text-sm font-bold mb-1" for="min_order_quantity">
                        {{ __('admin.min_order_quantity') }}
                    </label>
                    <input type="number" 
                           name="min_order_quantity" 
                           id="min_order_quantity" 
                           value="{{ old('min_order_quantity', 1) }}"
                           class="shadow appearance-none border rounded w-full py-1.5 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           :class="{'border-red-500': errors.min_order_quantity}"
                           required>
                    <template x-if="errors.min_order_quantity">
                        <p class="text-red-500 text-xs italic" x-text="errors.min_order_quantity[0]"></p>
                    </template>
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700 text-sm font-bold mb-1" for="status">
                        {{ __('admin.status') }}
                    </label>
                    <select name="status" 
                            id="status" 
                            class="shadow appearance-none border rounded w-full py-1.5 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{'border-red-500': errors.status}">
                        <option value="published" {{ old('status', 'published') == 'published' ? 'selected' : '' }}>
                            {{ __('admin.status_published') }}
                        </option>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>
                            {{ __('admin.status_draft') }}
                        </option>
                    </select>
                    <template x-if="errors.status">
                        <p class="text-red-500 text-xs italic" x-text="errors.status[0]"></p>
                    </template>
                </div>
            </div>

            <!-- 右侧列 - 描述和图片 -->
            <div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        产品描述 <span class="text-gray-500">(选填)</span>
                    </label>
                    <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                </div>

                <!-- 图片上传 -->
                <div class="mb-3">
                    <label class="block text-gray-700 text-sm font-bold mb-1">
                        {{ __('admin.product_images') }}
                    </label>
                    <div 
                        class="border-2 border-dashed border-gray-300 rounded-lg p-3 text-center"
                        x-on:dragover.prevent="dragover = true"
                        x-on:dragleave.prevent="dragover = false"
                        x-on:drop.prevent="addFiles($event)"
                        x-bind:class="{'bg-blue-50 border-blue-300': dragover}"
                    >
                        <input type="file" 
                               id="images" 
                               name="images[]" 
                               multiple 
                               accept="image/*" 
                               class="hidden"
                               x-ref="fileInput"
                               x-on:change="addFiles($event)">
                        
                        <div class="py-4">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 mt-2 justify-center">
                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                    <span x-on:click="$refs.fileInput.click()">{{ __('admin.upload_images') }}</span>
                                </label>
                                <p class="pl-1">{{ __('admin.or_drag_drop') }}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ __('admin.image_requirements') }}</p>
                            <p class="text-xs text-gray-500">{{ __('admin.first_image_main') }}</p>
                            <p class="text-xs text-gray-500">{{ __('admin.max_images') }}</p>
                        </div>
                    </div>
                    
                    <!-- 预览区域 -->
                    <div x-show="previewImages.length > 0" class="mt-2">
                        <p class="text-sm font-medium text-gray-700 mb-1">{{ __('admin.image_preview') }}</p>
                        <div class="grid grid-cols-3 gap-2" id="imagePreviewContainer">
                            <template x-for="(image, index) in previewImages" :key="index">
                                <div class="relative border rounded">
                                    <img :src="image.preview" class="w-full h-16 object-cover rounded">
                                    <button 
                                        type="button" 
                                        class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center m-1 text-xs"
                                        x-on:click="removeImage(index)"
                                    >×</button>
                                    <span 
                                        x-show="index === 0" 
                                        class="absolute bottom-0 left-0 bg-blue-500 text-white text-xs px-1.5 py-0.5 m-1 rounded">
                                        {{ __('admin.main_image') }}
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <template x-if="errors.images">
                        <p class="text-red-500 text-xs italic" x-text="errors.images[0]"></p>
                    </template>
                </div>
            </div>
        </div>

        <!-- 全局表单错误提示 -->
        <div x-show="formError" class="mb-4 p-2 bg-red-100 border border-red-400 text-red-700 rounded">
            <p x-text="formError"></p>
        </div>

        <!-- 提交按钮 -->
        <div class="flex items-center justify-end mt-4">
            <button type="submit" 
                    x-bind:disabled="isLoading"
                    x-bind:class="{'opacity-50 cursor-not-allowed': isLoading}"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1.5 px-4 rounded focus:outline-none focus:shadow-outline text-sm">
                <span x-show="!isLoading">{{ __('admin.create_product') }}</span>
                <span x-show="isLoading">{{ __('admin.processing') }}...</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function productFormData() {
    return {
        previewImages: [],
        isLoading: false,
        dragover: false,
        maxImages: 6,
        errors: {},
        formError: null,
        feedback: null,
        formData: {
            name: '',
            // ... other form fields
        },
        nameError: '',
        
        addFiles(event) {
            this.dragover = false;
            const newFiles = event.dataTransfer ? 
                event.dataTransfer.files : 
                event.target.files;
                
            if (!newFiles || newFiles.length === 0) return;
            
            // 检查总数量限制
            if ((this.previewImages.length + newFiles.length) > this.maxImages) {
                alert('{{ __("admin.max_images_reached") }}');
                return;
            }
            
            for (let i = 0; i < newFiles.length; i++) {
                const file = newFiles[i];
                
                // 检查文件类型
                if (!file.type.startsWith('image/')) {
                    alert('{{ __("admin.invalid_image_type") }}');
                    continue;
                }
                
                // 检查文件大小（10MB）
                if (file.size > 10 * 1024 * 1024) {
                    alert('{{ __("admin.image_too_large") }}');
                    continue;
                }
                
                this.addPreview(file);
            }
            
            // 清空input，以便重复选择同一文件时也能触发change事件
            if (event.target) {
                event.target.value = '';
            }
        },
        
        addPreview(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewImages.push({
                    file: file,
                    preview: e.target.result
                });
            };
            reader.readAsDataURL(file);
        },
        
        removeImage(index) {
            this.previewImages.splice(index, 1);
        },
        
        submitForm(event) {
            this.isLoading = true;
            this.errors = {};
            this.formError = null;
            this.feedback = null;
            
            const form = event.target;
            const formData = new FormData();
            
            // 添加所有基本字段从 x-model 或直接从表单元素
            formData.append('name', form.elements.name.value);
            formData.append('category_id', form.elements.category_id.value);
            formData.append('price', form.elements.price.value);
            formData.append('min_order_quantity', form.elements.min_order_quantity.value);
            formData.append('status', form.elements.status.value);
            formData.append('description', form.elements.description.value);
            
            // 添加新图片
            this.previewImages.forEach((imgData) => {
                // 使用相同的键名 'images[]'，让后端PHP能正确解析为数组
                formData.append('images[]', imgData.file);
            });
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json' // 明确期望JSON响应
                },
                body: formData
            })
            .then(response => {
                // 首先获取响应状态和尝试解析JSON
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json().then(data => ({
                        ok: response.ok,
                        status: response.status,
                        data: data
                    }));
                } else {
                    // 如果不是JSON，则可能是HTML错误页或其他，直接抛出错误
                    return response.text().then(text => {
                        throw new Error(`服务器响应不是JSON格式。状态: ${response.status}. 内容: ${text.substring(0, 200)}...`);
                    });
                }
            })
            .then(result => {
                this.isLoading = false;
                if (result.ok && result.data.success) {
                    this.feedback = result.data.message || '{{ __("admin.product_created") }}';
                    this.showSuccessMessage(this.feedback);
                    // 清空表单或重置 x-data 中的字段
                    this.previewImages = []; // 清空预览
                    form.reset(); // 重置原生表单元素
                    this.formData.name = ''; // 清空 x-model 绑定的数据
                    // ... 根据需要清空其他 this.formData 字段
                    this.errors = {};
                    this.formError = null;
                    
                    // 延迟重定向到产品列表，给用户时间看成功消息
                    setTimeout(() => {
                        window.location.href = '{{ route("admin.products.index") }}';
                    }, 2000);
                } else {
                    // 处理后端返回的JSON错误 (例如验证错误或业务逻辑错误)
                    this.formError = result.data.message || '{{ __("admin.product_creation_failed") }}';
                    if (result.data.errors) {
                        this.errors = result.data.errors;
                    } else {
                        this.errors = {}; // 清空旧的字段错误
                    }
                }
            })
            .catch(error => {
                this.isLoading = false;
                console.error('表单提交错误:', error);
                this.formError = error.message || '{{ __("admin.network_error") }}';
                this.errors = {};
            });
        },
        
        showSuccessMessage(message) {
            const alert = document.createElement('div');
            alert.className = 'fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 bg-green-100 text-green-700';
            alert.textContent = message;
            
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 3000);
        },

        // 检查产品名称是否重复
        async checkProductName(name) {
            if (!name) {
                this.nameError = '';
                return;
            }

            try {
                const response = await fetch('{{ route("admin.products.check-name") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ name })
                });

                const data = await response.json();
                this.nameError = data.exists ? data.message : '';
            } catch (error) {
                console.error('检查产品名称时出错:', error);
            }
        },
    };
}
</script>
@endpush 