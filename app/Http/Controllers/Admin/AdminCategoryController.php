<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    /**
     * 显示分类列表
     */
    public function index(): View
    {
        $categories = Category::orderBy('sort_order', 'asc')->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * 显示创建分类表单
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * 保存新分类
     */
    public function store(Request $request): RedirectResponse
    {
        // 验证请求数据
        $validated = $request->validate([
            'name_en' => 'required|string|max:50',
            'name_fr' => 'required|string|max:50',
            'show_on_home' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:1',
        ]);

        // 生成 slug
        $slug = Str::slug($validated['name_en']);
        $originalSlug = $slug;
        $counter = 1;

        // 检查 slug 唯一性
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter++;
        }

        $dataToCreate = [
            'name_en' => $validated['name_en'],
            'name_fr' => $validated['name_fr'],
            'slug' => $slug,
            'show_on_home' => $request->has('show_on_home'), // 复选框如果未选中则不提交该值
            'display_order' => $validated['display_order'] ?? 999,
        ];

        // 创建分类
        Category::create($dataToCreate);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', '分类创建成功！');
    }

    /**
     * 显示编辑分类表单
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * 更新分类
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        // 验证请求数据
        $validated = $request->validate([
            'name_en' => 'required|string|max:50',
            'name_fr' => 'required|string|max:50',
            'show_on_home' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:1',
        ]);

        $dataToUpdate = [
            'name_en' => $validated['name_en'],
            'name_fr' => $validated['name_fr'],
            'show_on_home' => $request->has('show_on_home'),
            'display_order' => $validated['display_order'] ?? 999,
        ];

        // 如果英文名称改变了，重新生成 slug
        if ($validated['name_en'] !== $category->name_en) {
            $slug = Str::slug($validated['name_en']);
            $originalSlug = $slug;
            $counter = 1;

            // 检查 slug 唯一性（排除当前分类）
            while (Category::where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists()) {
                $slug = $originalSlug.'-'.$counter++;
            }
            $dataToUpdate['slug'] = $slug;
        }

        // 更新分类
        $category->update($dataToUpdate);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', '分类更新成功！');
    }

    /**
     * 删除分类
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', '分类删除成功！');
    }

    /**
     * 移动分类（上移/下移）
     */
    public function move(Request $request, Category $category): RedirectResponse
    {
        $direction = $request->direction;

        if ($direction === 'up') {
            // 查找排序值比当前分类小的最大排序值的分类
            $prevCategory = Category::where('sort_order', '<', $category->sort_order)
                ->orderBy('sort_order', 'desc')
                ->first();

            if ($prevCategory) {
                // 交换它们的排序值
                $tempOrder = $category->sort_order;
                $category->sort_order = $prevCategory->sort_order;
                $prevCategory->sort_order = $tempOrder;

                $category->save();
                $prevCategory->save();
            }
        } elseif ($direction === 'down') {
            // 查找排序值比当前分类大的最小排序值的分类
            $nextCategory = Category::where('sort_order', '>', $category->sort_order)
                ->orderBy('sort_order', 'asc')
                ->first();

            if ($nextCategory) {
                // 交换它们的排序值
                $tempOrder = $category->sort_order;
                $category->sort_order = $nextCategory->sort_order;
                $nextCategory->sort_order = $tempOrder;

                $category->save();
                $nextCategory->save();
            }
        }

        return redirect()->route('admin.categories.index');
    }
}
