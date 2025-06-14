<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_can_delete_product_image()
    {
        // 创建管理员用户
        $admin = User::factory()->create(['is_admin' => true]);

        // 创建测试数据
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $image = ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_path' => 'products/test-image.jpg',
            'is_main' => true,
        ]);

        // 存储一个测试文件
        Storage::disk('public')->put('products/test-image.jpg', 'test content');

        // 执行删除请求
        $response = $this->actingAs($admin)->delete(route('admin.products.images.destroy', [
            'product' => $product->id,
            'image' => $image->id,
        ]));

        // 验证响应
        $response->assertJson(['success' => true]);

        // 验证数据库记录已删除
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);

        // 验证文件已从存储中删除
        Storage::disk('public')->assertMissing('products/test-image.jpg');
    }

    public function test_admin_cannot_delete_image_from_other_product()
    {
        // 创建管理员用户
        $admin = User::factory()->create(['is_admin' => true]);

        // 创建测试数据
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        $image = ProductImage::factory()->create([
            'product_id' => $product2->id,
            'image_path' => 'products/test-image.jpg',
            'is_main' => false,
        ]);

        // 存储一个测试文件
        Storage::disk('public')->put('products/test-image.jpg', 'test content');

        // 尝试从错误的产品删除图片
        $response = $this->actingAs($admin)->delete(route('admin.products.images.destroy', [
            'product' => $product1->id,
            'image' => $image->id,
        ]));

        // 验证响应
        $response->assertStatus(403);
        $response->assertJson(['success' => false]);

        // 验证图片仍然存在
        $this->assertDatabaseHas('product_images', ['id' => $image->id]);
        Storage::disk('public')->assertExists('products/test-image.jpg');
    }

    public function test_non_admin_cannot_delete_product_image()
    {
        // 创建普通用户
        $user = User::factory()->create(['is_admin' => false]);

        // 创建测试数据
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $image = ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_path' => 'products/test-image.jpg',
        ]);

        // 存储一个测试文件
        Storage::disk('public')->put('products/test-image.jpg', 'test content');

        // 尝试删除图片
        $response = $this->actingAs($user)->delete(route('admin.products.images.destroy', [
            'product' => $product->id,
            'image' => $image->id,
        ]));

        // 验证响应
        $response->assertStatus(403);

        // 验证图片仍然存在
        $this->assertDatabaseHas('product_images', ['id' => $image->id]);
        Storage::disk('public')->assertExists('products/test-image.jpg');
    }
}
