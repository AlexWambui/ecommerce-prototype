# Product and Categories explained

Products can belong to multiple categories.

Each product has one primary category (used for breadcrumbs, SEO, URLs).

Categories can have a hierachical tree (parent/child).

## DB Structure

Product Categories Table

```php
Schema::create('product_categories', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('title')->unique();
    $table->string('slug')->unique();
    $table->string('description')->nullable();
    $table->string('image')->nullable();
    $table->unsignedSmallInteger('sort_order')->default(0);

    $table->foreignId('parent_id')->nullable()
          ->constrained('product_categories')
          ->nullOnDelete();
    $table->timestamps();
});
```

- parent_id -> allows category hierachy.

Products Table

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('title')->unique();
    $table->string('slug')->unique();
    $table->string('product_code')->nullable();
    $table->string('sku')->unique()->nullable();
    $table->decimal('selling_price', 10, 2)->default(0.00);
    $table->decimal('discount_price', 10, 2)->nullable();
    $table->boolean('is_featured')->default(false);
    $table->boolean('is_visible')->default(true);
    $table->timestamps();
});
```

- category info is not stored here.
- all category relationships go to the pivot table.

Pivot Table (category_product)

```php
Schema::create('category_product', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
    $table->foreignId('product_category_id')->constrained('product_categories')->cascadeOnDelete();
    $table->boolean('is_primary')->default(false);
    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['product_id', 'product_category_id']);
});
```

- This allows products to have many categories.
- is_primary -> flags one category as the main one.
- This table connects products and categories many-to-many + tracks the primary category.

## Models

Product Category Model

```php
class ProductCategory extends Model
{
    protected $guarded = [];

    // Relationships
    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product')
                    ->withPivot('is_primary', 'sort_order')
                    ->withTimestamps();
    }

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    // Recursive method to get all ancestors (for breadcrumbs)
    public function ancestors()
    {
        $ancestors = collect();
        $current = $this;
        while ($current->parent) {
            $current = $current->parent;
            $ancestors->push($current);
        }
        return $ancestors->reverse();
    }
}
```

Product Model

```php
class Product extends Model
{
    protected $guarded = [];

    // Relationships
    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'category_product')
                    ->withPivot('is_primary', 'sort_order')
                    ->withTimestamps();
    }

    public function primaryCategory()
    {
        return $this->belongsToMany(ProductCategory::class, 'category_product')
                    ->wherePivot('is_primary', true);
    }

    // Accessor for easy usage
    public function getPrimaryCategoryAttribute()
    {
        return $this->primaryCategory()->first();
    }

    // Methods to manage categories
    public function syncCategories(array $categoryIds, $primaryCategoryId = null)
    {
        // Sync all categories
        $this->categories()->sync($categoryIds);

        // Set primary category
        if ($primaryCategoryId && in_array($primaryCategoryId, $categoryIds)) {
            $this->categories()->updateExistingPivot($categoryIds, ['is_primary' => false]);
            $this->categories()->updateExistingPivot($primaryCategoryId, ['is_primary' => true]);
        } elseif (!empty($categoryIds)) {
            $this->categories()->updateExistingPivot($categoryIds[0], ['is_primary' => true]);
        }
    }
}
```

- syncCategories() handles both multiple categories and primary category.
- Accessing $product->primaryCategory returns the main category.

## Code Implementation Samples

### Controllers

```php
<?php

namespace App\Http\Controllers;

use App\Models\Products\Product;
use App\Models\Products\ProductCategory;

class ProductController extends Controller
{
    // Get products by category (including subcategories)
    public function productsByCategory($categorySlug)
    {
        $category = ProductCategory::where('slug', $categorySlug)->firstOrFail();
        
        // Get all category IDs including descendants
        $categoryIds = $this->getCategoryIdsWithDescendants($category);
        
        // Get products in any of these categories
        $products = Product::whereHas('categories', function($query) use ($categoryIds) {
            $query->whereIn('product_categories.id', $categoryIds);
        })->paginate(12);
        
        return view('products.category', compact('products', 'category'));
    }
    
    // Helper to get all descendant category IDs
    private function getCategoryIdsWithDescendants($category)
    {
        $ids = [$category->id];
        
        foreach ($category->descendants as $descendant) {
            $ids[] = $descendant->id;
        }
        
        return $ids;
    }
    
    // Create product with categories
    public function store(Request $request)
    {
        $product = Product::create($request->all());
        
        // Attach categories
        $categoryIds = $request->input('category_ids', []);
        $primaryCategoryId = $request->input('primary_category_id');
        
        $product->syncCategories($categoryIds, $primaryCategoryId);
        
        return redirect()->route('products.show', $product);
    }
    
    // Update product categories
    public function updateCategories(Request $request, Product $product)
    {
        $categoryIds = $request->input('category_ids', []);
        $primaryCategoryId = $request->input('primary_category_id');
        
        $product->syncCategories($categoryIds, $primaryCategoryId);
        
        return response()->json(['success' => true]);
    }
}
```

example 2 for the controller:

```php
public function store(Request $request)
{
    $data = $request->validate([
        'title' => 'required|string|max:255',
        'category_ids' => 'required|array|min:1',
        'category_ids.*' => 'exists:product_categories,id',
        'primary_category_id' => 'required|exists:product_categories,id',
        // other product fields...
    ]);

    $product = Product::create($data);

    // Sync categories
    $product->syncCategories($data['category_ids'], $data['primary_category_id']);

    return redirect()->route('products.index')->with('success', 'Product created.');
}

public function update(Request $request, Product $product)
{
    $data = $request->validate([
        'title' => 'required|string|max:255',
        'category_ids' => 'required|array|min:1',
        'category_ids.*' => 'exists:product_categories,id',
        'primary_category_id' => 'required|exists:product_categories,id',
        // other product fields...
    ]);

    $product->update($data);

    // Sync categories
    $product->syncCategories($data['category_ids'], $data['primary_category_id']);

    return redirect()->route('products.index')->with('success', 'Product updated.');
}
```

### Blade Views

{{-- Show product categories --}}

```php
@foreach($product->categories as $category)
    <span class="badge bg-secondary">
        {{ $category->title }}
        @if($category->pivot->is_primary)
            <span class="text-warning">★</span>
        @endif
    </span>
@endforeach
```

{{-- Show category breadcrumb --}}

```php
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @foreach($category->breadcrumb as $crumb)
            @if($loop->last)
                <li class="breadcrumb-item active">{{ $crumb->title }}</li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{ route('categories.show', $crumb) }}">{{ $crumb->title }}</a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
```

{{-- Category tree for forms --}}

```php
<select name="category_ids[]" multiple class="form-select">
    @foreach($categories as $category)
        <option value="{{ $category->id }}" 
                @if(in_array($category->id, $selectedCategories)) selected @endif>
            {{ str_repeat('—', $category->depth) }} {{ $category->title }}
        </option>
    @endforeach
</select>
```

## How it works in practice

- Admin selects multiple categories.
- Admin chooses one primary category.
- Controller validates and calls syncCategories().
- Pivot table updates automatically.
- $product->primaryCategory now always returns the main category.
- $product->categories returns all categories.
