<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_services', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->references('id')->on('products')->cascadeOnDelete();
            $table->enum('type', [1, 2]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_services');
    }
};
