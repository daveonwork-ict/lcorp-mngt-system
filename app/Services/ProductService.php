<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImei;
use App\Models\ProductPriceHistory;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function categories(array $filters = []): LengthAwarePaginator
    {
        return ProductCategory::query()->orderBy('sort_order')->paginate(20);
    }

    public function brands(): LengthAwarePaginator
    {
        return Brand::query()->latest('id')->paginate(20);
    }

    public function products(array $filters = []): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'brand'])
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['brand_id'] ?? null, fn ($q, $id) => $q->where('brand_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($sub) use ($search): void {
                $sub->where('product_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            }))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function createCategory(array $data): ProductCategory
    {
        $category = ProductCategory::query()->create($data);
        $this->auditLogService->record('inventory_category', 'category_created', [], $category->toArray(), null, 'Category created');

        return $category;
    }

    public function updateCategory(ProductCategory $category, array $data): ProductCategory
    {
        $before = $category->toArray();
        $category->update($data);
        $this->auditLogService->record('inventory_category', 'category_updated', $before, $category->toArray(), null, 'Category updated');

        return $category;
    }

    public function createBrand(array $data): Brand
    {
        $brand = Brand::query()->create($data);
        $this->auditLogService->record('inventory_brand', 'brand_created', [], $brand->toArray(), null, 'Brand created');

        return $brand;
    }

    public function updateBrand(Brand $brand, array $data): Brand
    {
        $before = $brand->toArray();
        $brand->update($data);
        $this->auditLogService->record('inventory_brand', 'brand_updated', $before, $brand->toArray(), null, 'Brand updated');

        return $brand;
    }

    public function createProduct(array $data): Product
    {
        if (! empty($data['is_serialized']) && empty($data['is_imei_required'])) {
            $data['is_imei_required'] = true;
        }

        $data['created_by'] = auth()->id();
        $product = Product::query()->create($data);

        $this->auditLogService->record('inventory_product', 'product_created', [], $product->toArray(), null, 'Product created');

        return $product;
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $before = $product->toArray();

        if (! empty($data['is_serialized']) && empty($data['is_imei_required'])) {
            $data['is_imei_required'] = true;
        }

        $data['updated_by'] = auth()->id();
        $product->update($data);

        if (
            (string) $before['cost_price'] !== (string) $product->cost_price ||
            (string) $before['selling_price'] !== (string) $product->selling_price
        ) {
            ProductPriceHistory::query()->create([
                'product_id' => $product->id,
                'old_cost_price' => $before['cost_price'],
                'new_cost_price' => $product->cost_price,
                'old_selling_price' => $before['selling_price'],
                'new_selling_price' => $product->selling_price,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'remarks' => 'Updated from product edit',
            ]);

            $this->auditLogService->record('inventory_product', 'price_changed', $before, $product->toArray(), null, 'Product price changed');
        }

        $this->auditLogService->record('inventory_product', 'product_updated', $before, $product->toArray(), null, 'Product updated');

        return $product;
    }

    public function addImei(array $data): ProductImei
    {
        $imei = ProductImei::query()->create($data);

        $this->auditLogService->record('inventory_imei', 'imei_added', [], $imei->toArray(), $imei->branch_id, 'IMEI/serial added');

        return $imei;
    }

    public function updateImeiStatus(ProductImei $imei, string $status): ProductImei
    {
        $before = $imei->toArray();
        $imei->update(['status' => $status]);

        $this->auditLogService->record('inventory_imei', 'imei_updated', $before, $imei->toArray(), $imei->branch_id, 'IMEI status updated');

        return $imei;
    }
}
