<?php

use App\Models\Address;
use App\Models\User;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->references('id')->on('users')->cascadeOnDelete();
            $table->string('reference', 20)->default('-');
            $table->string('name', 100)->default('-');
            $table->string('phone', 15)->default('-');
            $table->tinyInteger('delivery_service')->default(1);
            $table->string('address', 150)->default('-');
            $table->integer('total')->default(0);
            $table->tinyInteger('status')->default(1)->comment("1: Order, 2: Packing, 3: Send, 4: Receive");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
