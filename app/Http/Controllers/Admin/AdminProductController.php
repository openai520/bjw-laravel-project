<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AdminProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->query('search');
        $categoryId = $request->query('category_id');
        $status = $request->query('status');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        $query = Product::with([
            'category',
            'images' => fn($query) => $query->where('is_main', true)
        ])->withCount([
            'views as view_count',
            'views as today_view_count' => fn($query) => $query->whereDate('viewed_at', today())
        ])->latest();

        // 应用搜索条件
        if ($searchTerm) {
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        // 应用分类筛选
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // 应用状态筛选
        if ($status) {
            $query->where('status', $status);
        }
        
        // 应用价格筛选
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        $products = $query->paginate(15);
        $categories = Category::orderBy('name_en')->get();

        return view('admin.products.index', compact('products', 'searchTerm', 'categoryId', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // 获取所有分类并按名称排序
        $categories = Category::orderBy('name_en')->get();

        // 打印调试信息
        \Log::debug('Categories query result:', ['count' => $categories->count(), 'data' => $categories->toArray()]);

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 记录请求信息
        \Log::debug('Product creation request:', [
            'has_files' => $request->hasFile('images'),
            'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
            'post_data' => $request->except('images'),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                \Log::debug("Image file {$index} info:", [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'is_valid' => $file->isValid(),
                    'error' => $file->getError()
                ]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'min_order_quantity' => 'required|integer|min:1',
            'status' => 'required|in:published,draft',
            'images' => 'nullable|array|max:6',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB
        ]);

        \Log::debug('Validation passed');

        try {
            DB::beginTransaction();

            // 创建产品
            $product = Product::create([
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'min_order_quantity' => $validated['min_order_quantity'],
                'status' => $validated['status'],
            ]);

            \Log::debug('Product base info created', ['product_id' => $product->id]);

            // 处理图片上传
            if ($request->hasFile('images')) {
                $isFirst = true;
                foreach ($request->file('images') as $index => $image) {
                    try {
                        $path = $image->store('products', 'public');
                        \Log::debug("Stored image {$index}", ['path' => $path]);

                        $productImage = new ProductImage([
                            'image_path' => $path,
                            'is_main' => $isFirst
                        ]);

                        $product->images()->save($productImage);
                        \Log::debug("Image {$index} saved to database", ['is_main' => $isFirst]);

                        $isFirst = false;
                    } catch (\Exception $e) {
                        \Log::error("Error processing image {$index}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            }

            DB::commit();
            \Log::debug('Product creation completed successfully');

            // Cache::tags(['products.list'])->flush(); // 清除产品列表相关缓存
            Cache::flush(); // 清除所有应用缓存，因为驱动不支持标签

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('admin.product_created'),
                    'product_id' => $product->id, // 可以选择性返回产品ID或其他数据
                    // 如果需要重定向URL给前端处理
                    // 'redirect_url' => route('admin.products.index', ['page' => $request->input('page')]) 
                ], 201); // 201 Created
            }

            $redirectRoute = route('admin.products.index');
            if ($request->filled('page')) {
                $redirectRoute .= '?page=' . $request->input('page');
            }

            return redirect($redirectRoute)
                ->with('success', __('admin.product_created'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::warning('Product creation validation failed:', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('admin.product_validation_failed'),
                    'errors' => $e->errors()
                ], 422); // 422 Unprocessable Entity
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Product creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('admin.product_creation_failed_server') // 使用更具体的错误消息键
                ], 500); // 500 Internal Server Error
            }

            return redirect()->back()
                ->with('error', __('admin.product_creation_failed'))
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        // 确保加载关联数据
        $product->loadMissing(['category', 'images']);

        // 获取所有分类并按名称排序
        $categories = Category::orderBy('name_en')->get();

        // 打印调试信息
        \Log::debug('Product edit data:', [
            'product_id' => $product->id,
            'category_id' => $product->category_id,
            'has_images' => $product->images->isNotEmpty(),
            'image_count' => $product->images->count(),
            'categories_count' => $categories->count()
        ]);

        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        Log::debug('Starting product update process', ['product_id' => $product->id]);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'min_order_quantity' => 'required|integer|min:1',
            'status' => 'required|in:draft,published',
            'images' => 'nullable|array|max:6',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'main_image_id' => 'nullable|exists:product_images,id'
        ]);

        Log::debug('Validation passed', $validated);

        try {
            DB::beginTransaction();

            // 更新产品基本信息
            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'price' => $validated['price'],
                'min_order_quantity' => $validated['min_order_quantity'],
                'status' => $validated['status'] ?? 'published',
            ]);

            Log::debug('Product basic info updated');

            // 处理要删除的图片
            if ($request->has('remove_images')) {
                $removeImageIds = $request->input('remove_images');
                Log::debug('Processing image removal', ['image_ids' => $removeImageIds]);

                $imagesToDelete = $product->images()->whereIn('id', $removeImageIds)->get();
                foreach ($imagesToDelete as $image) {
                    try {
                        // 删除物理文件
                        if (Storage::disk('public')->exists($image->image_path)) {
                            Storage::disk('public')->delete($image->image_path);
                            Log::debug('Deleted physical file', ['path' => $image->image_path]);
                        } else {
                            Log::warning('Physical file not found', ['path' => $image->image_path]);
                        }

                        // 删除数据库记录
                        $image->delete();
                        Log::debug('Deleted image record', ['image_id' => $image->id]);
                    } catch (\Exception $e) {
                        Log::error('Failed to delete image', [
                            'image_id' => $image->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // 处理新上传的图片
            if ($request->hasFile('images')) {
                Log::debug('Processing new image uploads');
                $isFirstImage = !$product->images()->where('is_main', true)->exists();

                foreach ($request->file('images') as $index => $image) {
                    Log::debug("Processing image {$index}", [
                        'original_name' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                        'mime_type' => $image->getMimeType()
                    ]);

                    if (!$image->isValid()) {
                        Log::warning("Image {$index} is not valid");
                        continue;
                    }

                    try {
                        $path = $image->store('products', 'public');

                        $productImage = new ProductImage([
                            'image_path' => $path,
                            'is_main' => $isFirstImage
                        ]);

                        $product->images()->save($productImage);
                        Log::debug('New image saved', ['path' => $path, 'is_main' => $isFirstImage]);

                        $isFirstImage = false;
                    } catch (\Exception $e) {
                        Log::error('Failed to save image', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            }

            // 更新主图
            if ($request->filled('main_image_id')) {
                $mainImageId = $request->input('main_image_id');
                Log::debug('Updating main image', ['image_id' => $mainImageId]);

                // 先将所有图片设置为非主图
                $product->images()->update(['is_main' => false]);

                // 设置新的主图
                $product->images()->where('id', $mainImageId)->update(['is_main' => true]);
                Log::debug('Main image updated');
            }

            DB::commit();
            Log::debug('Product update completed successfully');

            // Cache::tags(['products.list'])->flush(); // 清除产品列表相关缓存
            Cache::flush(); // 清除所有应用缓存
            Cache::forget('products.detail.' . $product->id); // 清除当前产品详情缓存

            $redirectRoute = route('admin.products.index');
            if ($request->filled('page')) {
                $redirectRoute .= '?page=' . $request->input('page');
            }

            // 根据请求类型返回不同的响应
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('admin.product_updated'),
                    'redirect' => $redirectRoute
                ]);
            }

            return redirect($redirectRoute)
                ->with('success', __('admin.product_updated'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('admin.product_update_failed'),
                    'errors' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->with('error', __('admin.product_update_failed'))
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        try {
            // 先记录产品ID，因为删除后可能无法访问
            $productId = $product->id;

            // 删除产品
            $product->delete();

            // Cache::tags(['products.list'])->flush(); // 清除产品列表相关缓存
            Cache::flush(); // 清除所有应用缓存
            Cache::forget('products.detail.' . $productId); // 清除当前产品详情缓存

            $redirectRoute = route('admin.products.index');
            if ($request->filled('page')) {
                $redirectRoute .= '?page=' . $request->input('page');
            }

            return redirect($redirectRoute)
                ->with('success', '产品删除成功');
        } catch (\Exception $e) {
            $redirectRoute = route('admin.products.index');
            if ($request->filled('page')) {
                $redirectRoute .= '?page=' . $request->input('page');
            }
            return redirect($redirectRoute)
                ->with('error', '删除产品时发生错误');
        }
    }

    /**
     * 删除产品图片
     */
    public function destroyImage(Product $product, $image)
    {
        // 检查用户是否有权限
        if (!auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => '无权限执行此操作'
            ], 403);
        }

        try {
            $image = ProductImage::findOrFail($image);

            // 确保图片属于当前产品
            if ($image->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => '图片不属于该产品'
                ], 403);
            }

            // 如果是主图，且还有其他图片，则将第一张非主图设为主图
            if ($image->is_main) {
                $nextImage = $product->images()
                    ->where('id', '!=', $image->id)
                    ->first();

                if ($nextImage) {
                    $nextImage->update(['is_main' => true]);
                }
            }

            // 删除物理文件
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            // 删除数据库记录
            $image->delete();

            return response()->json([
                'success' => true,
                'message' => '图片删除成功'
            ]);

        } catch (\Exception $e) {
            \Log::error('删除产品图片失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '删除失败，请重试'
            ], 500);
        }
    }

    /**
     * 批量删除指定资源
     */
    public function batchDestroy(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:products,id'
            ]);

            // 记录批量删除操作
            Log::info('Batch product deletion requested', [
                'user_id' => auth()->id(),
                'product_count' => count($validated['ids']),
                'product_ids' => $validated['ids']
            ]);

            // 执行批量删除
            $count = Product::whereIn('id', $validated['ids'])->delete();

            return response()->json([
                'success' => true,
                'message' => __('admin.products_deleted', ['count' => $count]),
                'deleted_count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Batch product deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('admin.products_deletion_failed')
            ], 500);
        }
    }

    /**
     * 批量更新产品状态
     */
    public function batchUpdateStatus(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:products,id',
                'status' => 'required|in:published,draft'
            ]);

            // 记录批量更新状态操作
            Log::info('Batch product status update requested', [
                'user_id' => auth()->id(),
                'product_count' => count($validated['ids']),
                'product_ids' => $validated['ids'],
                'new_status' => $validated['status']
            ]);

            // 执行批量更新
            $count = Product::whereIn('id', $validated['ids'])
                ->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => __('admin.products_status_updated', ['count' => $count]),
                'updated_count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Batch product status update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('admin.products_status_update_failed')
            ], 500);
        }
    }

    /**
     * 检查产品名称是否已存在
     */
    public function checkName(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $exists = Product::where('name', $validated['name'])->exists();

            return response()->json([
                'exists' => $exists,
                'message' => $exists ? '产品名称已存在，请使用其他名称' : ''
            ]);
        } catch (\Exception $e) {
            Log::error('Product name check failed', [
                'error' => $e->getMessage(),
                'name' => $request->input('name')
            ]);

            return response()->json([
                'exists' => false,
                'message' => '检查产品名称时发生错误'
            ], 500);
        }
    }

    /**
     * 快速更新产品价格
     */
    public function updatePrice(Request $request, Product $product): JsonResponse
    {
        try {
            $validated = $request->validate([
                'price' => 'required|numeric|min:0'
            ]);

            $product->update([
                'price' => $validated['price']
            ]);

            return response()->json([
                'success' => true,
                'message' => '价格更新成功',
                'price' => $validated['price']
            ]);
        } catch (\Exception $e) {
            Log::error('Product price update failed', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'price' => $request->input('price')
            ]);

            return response()->json([
                'success' => false,
                'message' => '更新价格失败'
            ], 500);
        }
    }

    /**
     * 删除指定产品图片
     */
}
