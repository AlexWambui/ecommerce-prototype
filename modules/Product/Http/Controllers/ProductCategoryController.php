<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Exception;
use Modules\Product\Models\ProductCategory;
use Modules\Product\Http\Requests\ProductCategoryRequest;

class ProductCategoryController extends Controller
{
    public function index()
    {
        return inertia('app/products/categories/Index');
    }

    public function create()
    {
        return inertia('app/products/categories/Create');
    }

    public function store(ProductCategoryRequest $request)
    {
        try {
            DB::beginTransaction();

            ProductCategory::create([
                'name' => $request->name,
            ]);

            DB::commit();

            Inertia::flash('toast', [
                'type' => "success",
                'message' => "Product Category created successfully"
            ]);

            return to_route('product-categories.index');
        } catch (Exception $e) {
            DB::rollback();

            Inertia::flash('toast', [
                'type' => "error",
                'message' => "Failed to save category: {$e->getMessage()}"
            ]);

            return back()->withInput();
        }
    }
}