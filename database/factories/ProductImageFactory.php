<?php

namespace Database\Factories;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    /**
     * 实际存在的图片文件名列表
     */
    protected $imageFiles = [
        'IMG_1070.JPG',
        'IMG_1072.JPG',
        'IMG_1311.JPG',
        'IMG_1312.JPG',
        'IMG_1575.JPG',
        'IMG_1578.JPG',
        'IMG_1753.JPG',
        'IMG_1754.JPG',
        'IMG_1756.JPG',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 随机选择一个实际存在的图片文件
        $imageFile = fake()->randomElement($this->imageFiles);
        $imagePath = 'products/'.$imageFile;

        // 生成缩略图路径 - 使用_thumb后缀作为命名约定
        $extension = pathinfo($imageFile, PATHINFO_EXTENSION);
        $fileName = pathinfo($imageFile, PATHINFO_FILENAME);
        $thumbnailPath = 'products/'.$fileName.'_thumb.'.$extension;

        return [
            'image_path' => $imagePath,
            'thumbnail_path' => $thumbnailPath,
            'is_main' => false,
            'sort_order' => 1,
        ];
    }
}
