@extends('admin.layouts.app')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">批量添加产品</h1>
        </div>

        <!-- 获取错误信息 -->
        @php
            $importErrorMessages = session('import_error_messages', []);
            $successCount = session('import_success_count', 0);
            $failureCount = session('import_failure_count', 0);
        @endphp

        <!-- 显示总体导入结果 -->
        @if ($successCount > 0 || $failureCount > 0)
            <div
                class="mb-6 p-4 rounded {{ $failureCount > 0 ? 'bg-yellow-100 border border-yellow-400 text-yellow-700' : 'bg-green-100 border border-green-400 text-green-700' }}">
                <p class="font-medium">导入结果：成功 {{ $successCount }} 个产品，失败 {{ $failureCount }} 个产品。</p>
                @if ($failureCount > 0)
                    <p class="mt-1 text-sm">请修正下方标记的错误后重新提交。</p>
                @endif
            </div>
        @endif

        <!-- 批量设置区域 -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">批量设置</h2>

            <div class="grid grid-cols-1 gap-6">
                <!-- 分类批量设置 -->
                <div class="flex flex-wrap items-end gap-x-4 gap-y-2 mb-4 border-b pb-4">
                    <div class="flex-shrink-0">
                        <label for="bulk-category" class="block text-sm font-medium text-gray-700 mb-1">选择分类</label>
                        <select id="bulk-category"
                            class="form-select rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="">-- 请选择分类 --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-shrink-0">
                        <label for="bulk-category-from" class="block text-sm font-medium text-gray-700 mb-1">从行</label>
                        <input type="number" min="1" max="5" id="bulk-category-from" placeholder="1"
                            class="form-input rounded-md border-gray-300 shadow-sm w-20 text-sm">
                    </div>
                    <div class="flex-shrink-0">
                        <label for="bulk-category-to" class="block text-sm font-medium text-gray-700 mb-1">到行</label>
                        <input type="number" min="1" max="5" id="bulk-category-to" placeholder="5"
                            class="form-input rounded-md border-gray-300 shadow-sm w-20 text-sm">
                    </div>
                    <div class="self-end">
                        <button type="button" id="apply-bulk-category"
                            class="bg-gray-600 hover:bg-gray-700 text-white py-1.5 px-3 rounded text-sm">应用分类</button>
                    </div>
                </div>

                <!-- 最小订购数量批量设置 -->
                <div class="flex flex-wrap items-end gap-x-4 gap-y-2">
                    <div class="flex-shrink-0">
                        <label for="bulk-min-qty" class="block text-sm font-medium text-gray-700 mb-1">最小订购数量</label>
                        <input type="number" min="1" id="bulk-min-qty"
                            class="form-input rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </div>
                    <div class="flex-shrink-0">
                        <label for="bulk-min-qty-from" class="block text-sm font-medium text-gray-700 mb-1">从行</label>
                        <input type="number" min="1" max="5" id="bulk-min-qty-from" placeholder="1"
                            class="form-input rounded-md border-gray-300 shadow-sm w-20 text-sm">
                    </div>
                    <div class="flex-shrink-0">
                        <label for="bulk-min-qty-to" class="block text-sm font-medium text-gray-700 mb-1">到行</label>
                        <input type="number" min="1" max="5" id="bulk-min-qty-to" placeholder="5"
                            class="form-input rounded-md border-gray-300 shadow-sm w-20 text-sm">
                    </div>
                    <div class="self-end">
                        <button type="button" id="apply-bulk-min-qty"
                            class="bg-gray-600 hover:bg-gray-700 text-white py-1.5 px-3 rounded text-sm">应用数量</button>
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-500">
                    输入行号范围（1-5）并点击"应用"按钮将设置应用到指定行。如果行号范围留空，则应用到所有行。您仍可单独编辑各行的对应字段。
                </div>
            </div>
        </div>

        <!-- 提交按钮 -->
        <div class="flex justify-end mb-6">
            <button type="submit" form="batch-upload-form" id="submit-button"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                提交全部产品
            </button>
        </div>

        <hr class="my-4 border-gray-300">

        <!-- 主表单 -->
        <form action="{{ route('admin.products.batch_import.handle') }}" method="POST" id="batch-upload-form"
            class="bg-white p-6 rounded-lg shadow">
            @csrf

            <!-- 表单头部 -->
            <div class="grid grid-cols-12 gap-4 mb-4 font-medium text-gray-700 text-sm">
                <div class="col-span-1">序号</div>
                <div class="col-span-2">商品名称*</div>
                <div class="col-span-2">分类*</div>
                <div class="col-span-1">价格*</div>
                <div class="col-span-1">最小订购量*</div>
                <div class="col-span-2">描述</div>
                <div class="col-span-3">图片 (最多6张)</div>
            </div>

            <!-- 产品行 -->
            @for ($i = 0; $i < 5; $i++)
                <div
                    class="grid grid-cols-12 gap-4 mb-3 items-start py-3 {{ $i % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} rounded {{ isset($importErrorMessages['row_' . ($i + 1)]) ? 'border border-red-300' : '' }}">
                    <div class="col-span-1 pt-2 text-gray-500 text-sm">{{ $i + 1 }}</div>

                    <div class="col-span-2">
                        <div class="relative">
                            <input type="text"
                                name="products[{{ $i }}][name]"
                                placeholder="商品名称 (必填)"
                                x-data="{ nameError: '' }"
                                x-on:input="checkProductName($event.target.value, $el)"
                                class="form-input w-full rounded border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                            <div x-show="nameError"
                                x-text="nameError"
                                class="absolute top-full left-0 text-red-500 text-xs mt-1 bg-white p-1 rounded shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="col-span-2">
                        <select name="products[{{ $i }}][category_id]"
                            class="form-select w-full rounded border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                            <option value="">-- 请选择分类 --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name_en }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-1">
                        <input type="number" step="0.01" min="0" name="products[{{ $i }}][price]"
                            placeholder="价格 (必填)"
                            class="form-input w-full rounded border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                    </div>

                    <div class="col-span-1">
                        <input type="number" min="1" name="products[{{ $i }}][min_order_quantity]"
                            placeholder="最小订购量"
                            class="form-input w-full rounded border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                    </div>

                    <div class="col-span-2">
                        <textarea name="products[{{ $i }}][description]" placeholder="描述 (选填)" rows="2"
                            class="form-textarea w-full rounded border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm"></textarea>
                    </div>

                    <div class="col-span-3">
                        <input type="file" class="filepond-input" data-row-index="{{ $i }}" multiple>
                        <div id="temp_images_container_{{ $i }}"></div>
                    </div>

                    <!-- 行级错误信息显示 -->
                    @if (isset($importErrorMessages['row_' . ($i + 1)]))
                        <div class="col-span-12 mt-1 text-red-600 text-sm bg-red-50 p-2 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            {{ $importErrorMessages['row_' . ($i + 1)] }}
                        </div>
                    @endif
                </div>
            @endfor
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM加载完成，初始化FilePond和事件监听器');

                // FilePond 初始化
                const pondInputs = document.querySelectorAll('.filepond-input');

                pondInputs.forEach(input => {
                    const rowIndex = input.getAttribute('data-row-index');
                    console.log('初始化FilePond，行索引:', rowIndex);

                    const pond = FilePond.create(input, {
                        allowMultiple: true,
                        maxFiles: 6,
                        name: 'filepond', // Important for server-side processing
                        imagePreviewHeight: 60,
                        stylePanelLayout: 'compact',
                        itemPanelAspectRatio: 1,
                        imageCropAspectRatio: '1:1',
                        server: {
                            process: {
                                url: '{{ route('admin.products.batch_upload.image') }}',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                onload: (response) => {
                                    // 解析响应获取文件标识符
                                    try {
                                        const responseData = JSON.parse(response);
                                        console.log('文件上传成功，标识符:', responseData.identifier);
                                        return responseData.identifier;
                                    } catch (error) {
                                        console.error('解析服务器响应时出错:', error);
                                        return null;
                                    }
                                },
                                onerror: (response) => {
                                    console.error('文件上传失败:', response);
                                    // 尝试解析错误信息
                                    let errorMessage = '上传失败';
                                    try {
                                        const errorData = JSON.parse(response);
                                        if (errorData && errorData.errors && errorData.errors
                                            .filepond) {
                                            errorMessage = errorData.errors.filepond[0];
                                        } else if (errorData && errorData.error) {
                                            errorMessage = errorData.error;
                                        }
                                    } catch (e) {
                                        /* Ignore parsing error */ }

                                    // 可以在这里向用户显示更具体的错误信息
                                    alert('文件上传出错: ' + errorMessage);

                                    return null;
                                }
                            }
                            // Note: Revert/Remove functionality is not explicitly handled here for simplicity.
                            // FilePond usually handles revert requests automatically if the server endpoint is configured.
                        }
                    });

                    // 文件上传成功处理
                    pond.on('processfile', (error, file) => {
                        if (error) {
                            console.error('文件处理错误:', error);
                            return;
                        }

                        const fileId = file.serverId; // 服务器返回的标识符
                        if (!fileId) {
                            console.error('服务器未返回有效的文件标识符');
                            return;
                        }

                        // 获取容器
                        const container = document.getElementById(`temp_images_container_${rowIndex}`);
                        if (!container) {
                            console.error(
                                `[JS ProcessFile] Row ${rowIndex}: Container not found! ID: temp_images_container_${rowIndex}`
                                );
                            return;
                        }

                        // 检查是否已存在具有此值的输入框
                        const existingInput = container.querySelector(`input[value="${fileId}"]`);
                        if (!existingInput) {
                            // 创建新的隐藏输入框
                            const newInput = document.createElement('input');
                            newInput.type = 'hidden';
                            // 注意name属性末尾的 []，这会让PHP将其解析为数组
                            newInput.name = `products[${rowIndex}][temp_image_identifiers][]`;
                            newInput.value = fileId;
                            container.appendChild(newInput);
                            console.log(
                                `[JS ProcessFile] Row ${rowIndex}: Added hidden input for ID ${fileId}`
                                );
                        }
                    });

                    // 文件被移除时处理
                    pond.on('removefile', (error, file) => {
                        const fileId = file.serverId;
                        if (!fileId) {
                            console.warn(
                                '[JS RemoveFile] No serverId found for removed file, cannot update hidden input.'
                                );
                            return;
                        }

                        // 获取容器
                        const container = document.getElementById(`temp_images_container_${rowIndex}`);
                        if (!container) {
                            console.error(
                                `[JS RemoveFile] Row ${rowIndex}: Container not found! ID: temp_images_container_${rowIndex}`
                                );
                            return;
                        }

                        // 查找并移除对应的隐藏输入框
                        const inputToRemove = container.querySelector(
                            `input[name="products[${rowIndex}][temp_image_identifiers][]"][value="${fileId}"]`
                            );
                        if (inputToRemove) {
                            container.removeChild(inputToRemove);
                            console.log(
                                `[JS RemoveFile] Row ${rowIndex}: Removed hidden input for ID ${fileId}`
                                );
                        } else {
                            console.warn(
                                `[JS RemoveFile] Row ${rowIndex}: Hidden input for ID ${fileId} not found for removal.`
                                );
                        }
                    });
                });

                function getLoopIndices(fromInputId, toInputId) {
                    const fromVal = document.getElementById(fromInputId).value;
                    const toVal = document.getElementById(toInputId).value;

                    let startIndex = 0; // 默认起始索引 (对应第 1 行)
                    let endIndex = 4; // 默认结束索引 (对应第 5 行)

                    // 如果两个输入框都有值，则尝试解析并验证范围
                    if (fromVal && toVal) {
                        const fromRow = parseInt(fromVal, 10); // 解析起始行号
                        const toRow = parseInt(toVal, 10); // 解析结束行号

                        // 验证范围是否有效
                        if (!isNaN(fromRow) && !isNaN(toRow) && // 确保是数字
                            fromRow >= 1 && toRow <= 5 && // 确保在 1-5 范围内
                            fromRow <= toRow) // 确保起始不大于结束
                        {
                            startIndex = fromRow - 1; // 将 1 基的行号转换为 0 基的索引
                            endIndex = toRow - 1;
                        } else {
                            // 如果范围无效，提示用户并返回 null
                            alert('输入的行号范围无效 (应在 1-5 之间，且起始行号小于等于结束行号)');
                            return null; // 表示验证失败，中断操作
                        }
                    } else if (fromVal || toVal) {
                        // 如果只填写了其中一个输入框，也视为无效范围
                        alert('请输入完整的起始和结束行号，或将两者留空以应用到所有行。');
                        return null; // 表示验证失败
                    }
                    // 如果两个输入框都为空，则使用默认值 (0, 4)，即应用到所有行

                    console.log(`准备应用到行: ${startIndex + 1} 到 ${endIndex + 1}`); // 调试日志
                    return {
                        startIndex,
                        endIndex
                    }; // 返回计算好的索引范围
                }

                // 批量应用分类逻辑
                document.getElementById('apply-bulk-category').addEventListener('click', function() {
                    console.log('点击了应用批量分类按钮');
                    const bulkCategoryValue = document.getElementById('bulk-category').value;
                    if (!bulkCategoryValue) {
                        alert('请先选择一个分类');
                        return;
                    }
                    const range = getLoopIndices('bulk-category-from', 'bulk-category-to');
                    if (range === null) return;

                    for (let i = range.startIndex; i <= range.endIndex; i++) {
                        const categorySelect = document.querySelector(
                            `select[name="products[${i}][category_id]"]`);
                        if (categorySelect) {
                            categorySelect.value = bulkCategoryValue;
                        }
                    }
                    alert(`分类已成功应用到第 ${range.startIndex + 1} 行至第 ${range.endIndex + 1} 行`);
                });

                // 批量应用最小订购量逻辑
                document.getElementById('apply-bulk-min-qty').addEventListener('click', function() {
                    console.log('点击了应用批量最小订购量按钮');
                    const bulkMinQtyValue = document.getElementById('bulk-min-qty').value;
                    if (!bulkMinQtyValue || parseInt(bulkMinQtyValue, 10) < 1) {
                        alert('请输入一个有效的最小订购数量 (必须大于等于 1)');
                        return;
                    }
                    const range = getLoopIndices('bulk-min-qty-from', 'bulk-min-qty-to');
                    if (range === null) return;

                    for (let i = range.startIndex; i <= range.endIndex; i++) {
                        const minQtyInput = document.querySelector(
                            `input[name="products[${i}][min_order_quantity]"]`);
                        if (minQtyInput) {
                            minQtyInput.value = bulkMinQtyValue;
                        }
                    }
                    alert(`最小订购量已成功应用到第 ${range.startIndex + 1} 行至第 ${range.endIndex + 1} 行`);
                });

                // 检查产品名称是否重复
                async function checkProductName(name, element) {
                    if (!name) {
                        element._x.$data.nameError = '';
                        return;
                    }

                    try {
                        const response = await fetch('{{ route('admin.products.check-name') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify({
                                name
                            })
                        });

                        const data = await response.json();
                        element._x.$data.nameError = data.exists ? data.message : '';
                    } catch (error) {
                        console.error('检查产品名称时出错:', error);
                    }
                }

                window.checkProductName = checkProductName;
            });
        </script>
    @endpush

@endsection
