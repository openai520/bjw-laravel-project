<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeCategoryFeaturedProduct;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminHomeSettingController extends Controller
{
    /**
     * Display a listing of the home page settings, specifically for category featured products.
     */
    public function index(): View
    {
        $categories = Category::with(['homeFeaturedProducts.product' => function ($query) {
            $query->select('id', 'name'); // 只选择产品ID和名称以提高效率
        }])
            ->where('show_on_home', true) // 保留此行，只获取标记为在首页显示的分类
            ->orderBy('display_order')
            ->orderBy('name_en')
            ->get();

        return view('admin.home_settings.index', compact('categories'));
    }

    /**
     * Show the form for editing featured products for a specific category.
     */
    public function editFeaturedProducts(Category $category): View
    {
        // 加载当前分类已推荐的产品，并按顺序排序
        $category->load([
            'homeFeaturedProducts' => function ($query) {
                $query->orderBy('display_order');
            },
            'homeFeaturedProducts.product', // 预加载产品信息
        ]);

        // 获取该分类下的所有可用产品（排除已推荐的，可选，或者在前端处理）
        // 为了简单起见，我们先获取所有产品，让用户在前端选择
        $allProducts = Product::where('category_id', $category->id)
            ->where('status', 'published') // 通常只推荐已发布的产品
            ->orderBy('name')
            ->select('id', 'name') // 只需ID和名称用于选择
            ->get();

        return view('admin.home_settings.edit_featured_products', compact('category', 'allProducts'));
    }

    /**
     * Update the featured products for a specific category.
     */
    public function updateFeaturedProducts(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'products' => 'nullable|array|max:5', // 最多5个产品
            'products.*.product_id' => 'required_with:products|exists:products,id',
            'products.*.display_order' => 'required_with:products|integer|min:1|max:5',
            // 添加自定义规则来检查 display_order 的唯一性
            'products.*.display_order' => [
                'required_with:products',
                'integer',
                'min:1',
                'max:5',
                function ($attribute, $value, $fail) use ($request) {
                    // $attribute 会是类似 'products.0.display_order'
                    // 我们需要收集所有提交的 display_order 并检查重复
                    $allDisplayOrders = collect($request->input('products'))
                        ->pluck('display_order')
                        ->filter() // 过滤掉 null或空值
                        ->all();
                    if (count($allDisplayOrders) !== count(array_unique($allDisplayOrders))) {
                        $fail('显示顺序 (Display Order) 不能重复。');
                    }
                },
            ],
        ], [
            'products.*.display_order.integer' => '显示顺序必须是数字。',
            'products.*.display_order.min' => '显示顺序必须至少为1。',
            'products.*.display_order.max' => '显示顺序不能超过5。',
            'products.*.product_id.required_with' => '选择了产品时，产品ID不能为空。',
            'products.*.display_order.required_with' => '选择了产品时，显示顺序不能为空。',
        ]);

        DB::beginTransaction();
        try {
            // 先删除该分类旧的推荐产品设置
            HomeCategoryFeaturedProduct::where('category_id', $category->id)->delete();

            if ($request->has('products') && ! empty($validated['products'])) {
                // 再次检查已验证数据中的 display_order 唯一性，以防万一
                // （理论上自定义验证规则已处理，但多一层保险无害）
                $submittedDisplayOrders = array_column($validated['products'], 'display_order');
                if (count($submittedDisplayOrders) !== count(array_unique($submittedDisplayOrders))) {
                    // 这应该由上面的验证规则捕获，但作为后备
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'products' => '显示顺序提交存在重复，请修正。',
                    ]);
                }

                foreach ($validated['products'] as $featuredProductData) {
                    // 如果 product_id 或 display_order 为空（可能在 nullabe 数组中发生），则跳过
                    if (empty($featuredProductData['product_id']) || empty($featuredProductData['display_order'])) {
                        continue;
                    }

                    HomeCategoryFeaturedProduct::create([
                        'category_id' => $category->id,
                        'product_id' => $featuredProductData['product_id'],
                        'display_order' => $featuredProductData['display_order'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.home_settings.index')->with('success', '首页分类推荐产品更新成功！');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Failed to update featured products for category '.$category->id.': '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with('error', '更新失败，发生服务器错误：'.$e->getMessage())->withInput();
        }
    }

    // Store, Show, Edit, Update, Destroy 等方法可以根据需要保留或删除
    // 我们主要用 index, editFeaturedProducts, updateFeaturedProducts
    // destroy 等方法可能用于删除整个分类的推荐设置，但暂时用不到

    // Resource controller methods (index, create, store, show, edit, update, destroy)
    // We will primarily use index, and custom methods for featured products.

    public function create()
    {
        abort(404);
    }

    public function store(Request $request)
    {
        abort(404);
    }

    public function show($id)
    {
        abort(404);
    }

    public function edit($id)
    {
        abort(404);
    }

    public function update(Request $request, $id)
    {
        abort(404);
    }

    public function destroy($id)
    {
        abort(404);
    }
}
