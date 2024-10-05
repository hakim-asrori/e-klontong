<?php

namespace App\Http\Controllers;

use App\Facades\MessageFixer;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    protected $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function index(Request $request)
    {
        $query = $this->category->query();

        if ($request->has('search')) {
            $query->where("name", "LIKE", "%$request->search%");
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('category_id')) {
            $query->where('id', $request->category_id);
        }

        if ($request->has('slug')) {
            $query->where('slug', $request->slug);
        }

        $query->where('status', 1);

        $countCategory = $query->count();
        $categories = $query->paginate($request->per_page);

        $categories->getCollection()->transform(function ($category) {
            $image = url(Storage::url($category->image));

            $category->image = $image;

            return $category;
        });

        if ($request->has('category_id') || $request->has('slug')) {
            return $this->detail($categories->items());
        }

        return MessageFixer::render(
            code: $countCategory > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: $countCategory > 0 ? null : "Category no available.",
            data: $countCategory > 0 ? $categories->items() : null,
            paginate: ($categories instanceof LengthAwarePaginator) && $countCategory > 0  ? [
                "current_page" => $categories->currentPage(),
                "last_page" => $categories->lastPage(),
                "total" => $categories->total(),
                "from" => $categories->firstItem(),
                "to" => $categories->lastItem(),
            ] : null
        );
    }

    protected function detail($category)
    {
        return MessageFixer::render(
            code: count($category) > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: count($category) > 0 ? null : "Category no available.",
            data: count($category) > 0 ? $category[0] : null
        );
    }
}