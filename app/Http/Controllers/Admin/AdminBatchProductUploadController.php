<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessProductImageFromLocal;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminBatchProductUploadController extends Controller
{
    /**
     * 显示批量上传表单
     */
    public function showUploadForm()
    {
        // 获取所有分类
        $categories = Category::orderBy('name_en')->get();
        
        // 返回视图并传递分类数据
        return view('admin.products.batch_upload', compact('categories'));
    }

    /**
     * 处理临时图片上传
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function uploadTemporaryImage(Request $request)
    {
        // 验证上传的文件
        $validator = Validator::make($request->all(), [
            'filepond' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:10240', // 最大10MB
        ]);

        // 如果验证失败，返回错误响应
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // 确保目录存在
            $tempPath = 'temp/batch_images';
            if (!Storage::exists($tempPath)) {
                Storage::makeDirectory($tempPath);
            }

            // 获取上传的文件
            $file = $request->file('filepond');
            
            // 生成唯一文件名
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            
            // 存储文件到临时目录
            $path = $file->storeAs($tempPath, $filename);
            
            if (!$path) {
                return response()->json([
                    'error' => '文件存储失败'
                ], 500);
            }
            
            // 返回文件标识符
            return response()->json([
                'identifier' => $filename
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('临时图片上传失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => '上传处理失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 处理批量导入请求
     */
    public function handleBatchImport(Request $request)
    {
        try {
            // 检查请求是否为JSON格式
            $productsData = $request->isJson() ? $request->json('products', []) : $request->input('products', []);
            
            if (empty($productsData)) {
                return response()->json([
                    'success' => false,
                    'message' => '没有检测到有效的产品数据'
                ], 422);
            }

            $result = DB::transaction(function () use ($productsData) {
                $successCount = 0;
                $failureCount = 0;
                $errorMessages = [];

                foreach ($productsData as $key => $productData) {
                    // 过滤不完整的行
                    if (empty($productData['name']) || !isset($productData['price'])) {
                        continue;
                    }

                    // 验证数据
                    $validator = Validator::make($productData, [
                        'name' => 'required|string|max:255',
                        'price' => 'required|numeric|min:0',
                        'category_id' => 'required|integer|exists:categories,id',
                        'min_order_quantity' => 'required|integer|min:1',
                        'description' => 'nullable|string',
                        'temp_image_identifiers' => 'nullable|array',
                        'temp_image_identifiers.*' => 'string',
                        // 'main_image_identifier' => 'nullable|string|max:255', // 保持移除状态
                    ]);

                    // 如果验证失败
                    if ($validator->fails()) {
                        $failureCount++;
                        $rowNumber = $key + 1;
                        $errorMessages["row_{$rowNumber}"] = "第 {$rowNumber} 行数据验证失败: " . implode(', ', $validator->errors()->all());
                        
                        Log::warning('批量上传产品行验证失败', [
                            'row' => $rowNumber,
                            'errors' => $validator->errors()->all()
                        ]);
                        
                        continue;
                    }

                    try {
                        // 创建产品
                        $product = new Product();
                        $product->name = $productData['name'];
                        $product->price = $productData['price'];
                        $product->category_id = $productData['category_id'];
                        $product->min_order_quantity = $productData['min_order_quantity'];
                        $product->description = $productData['description'] ?? '';
                        $product->status = 'published';
                        $product->save();
                        Log::info("[BATCH] Product created/found: ID {$product->id}");

                        // Process temporary image identifiers
                        $tempIdentifiersInput = $productData['temp_image_identifiers'] ?? null;
                        $tempIdentifiers = [];
                        
                        // --- 解析 temp_image_identifiers (恢复旧逻辑，处理可能的数组或字符串) ---
                        if (is_array($tempIdentifiersInput)) {
                            $tempIdentifiers = array_filter($tempIdentifiersInput, function($value) { return !is_null($value) && $value !== '' && trim($value) !== ''; });
                        } elseif (is_string($tempIdentifiersInput) && !empty($tempIdentifiersInput)) {
                             // (如果FilePond意外提交字符串，尝试解析)
                            $tempIdentifiers = array_filter(explode(',', $tempIdentifiersInput), function($value) { return !is_null($value) && $value !== '' && trim($value) !== ''; });
                        }
                        
                        Log::info("[BATCH] Product ID {$product->id}: Parsed Identifiers Array:", $tempIdentifiers);

                        // --- 查找有效的临时文件路径 (保持不变) ---
                        $validImagePaths = [];
                        $identifierToPathMap = [];
                        foreach ($tempIdentifiers as $identifier) {
                            if (empty(trim($identifier))) continue;
                            $trimmedIdentifier = trim($identifier);
                            $tempPath = storage_path('app/temp/batch_images/' . $trimmedIdentifier);
                            if (file_exists($tempPath)) {
                                $validImagePaths[] = $tempPath;
                                $identifierToPathMap[$trimmedIdentifier] = $tempPath;
                            } else {
                                Log::warning("[BATCH] Product ID {$product->id}: Temp file NOT found: " . $tempPath);
                            }
                        }
                        Log::info("[BATCH] Product ID {$product->id}: Valid Paths Array:", $validImagePaths);

                        // If there are valid image paths, dispatch the image processing job
                        if (!empty($validImagePaths)) {
                            // --- 准备分发Job (移除 mainImageIdentifier) ---
                            Log::info("[BATCH] Product ID {$product->id}: Preparing to dispatch job."); // 恢复旧日志

                            // ProcessProductImageFromLocal::dispatch($product->id, $validImagePaths, $mainImageIdentifier) - 移除参数
                            ProcessProductImageFromLocal::dispatch($product->id, $validImagePaths)
                                ->onQueue('image_processing');

                            Log::info("[BATCH] Product ID {$product->id}: Job DISPATCHED."); // 恢复旧日志
                        } else {
                            Log::info("[BATCH] Product ID {$product->id}: No valid paths, job NOT dispatched.");
                        }

                        $successCount++;
                    } catch (\Exception $e) {
                        $failureCount++;
                        $rowNumber = $key + 1;
                        $errorMessages["row_{$rowNumber}"] = "第 {$rowNumber} 行保存失败: " . $e->getMessage();
                        Log::error('[BATCH] Exception during row processing for row ' . $rowNumber, [
                            'error' => $e->getMessage(),
                            'trace_snippet' => substr($e->getTraceAsString(), 0, 500), // Log snippet of trace
                            'data' => $productData
                        ]);
                    }
                }

                return [
                    'successCount' => $successCount,
                    'failureCount' => $failureCount,
                    'errorMessages' => $errorMessages
                ];
            });

            // 将结果存入闪存数据
            session()->flash('import_success_count', $result['successCount']);
            session()->flash('import_failure_count', $result['failureCount']);
            
            if ($result['failureCount'] > 0) {
                session()->flash('import_error_messages', $result['errorMessages']);
            }

            // 根据请求类型返回不同响应
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "成功导入 {$result['successCount']} 个产品" . 
                                ($result['failureCount'] > 0 ? "，失败 {$result['failureCount']} 个产品" : ""),
                    'redirect' => route('admin.products.batch_upload.form')
                ]);
            }

            // 重定向响应
            $message = "成功导入 {$result['successCount']} 个产品";
            if ($result['failureCount'] > 0) {
                $message .= "，失败 {$result['failureCount']} 个产品";
            }

            return redirect()->route('admin.products.batch_upload.form')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('[BATCH] General exception in handleBatchImport', [
                'error' => $e->getMessage(),
                'trace_snippet' => substr($e->getTraceAsString(), 0, 500)
            ]);

            // 根据请求类型返回不同响应
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '批量导入产品时发生错误：' . $e->getMessage()
                ], 500);
            }

            // 重定向响应
            return redirect()->route('admin.products.batch_upload.form')
                ->with('error', '批量导入产品时发生错误：' . $e->getMessage());
        }
    }
} 