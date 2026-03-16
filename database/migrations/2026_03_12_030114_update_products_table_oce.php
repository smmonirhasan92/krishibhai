<?php

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('product_type', ['physical', 'digital', 'medicine', 'service'])->default('physical'); // বহুমুখী ব্যবহারের জন্য
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null'); // কোম্পানি বা ব্র্যান্ডের নাম
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('sku')->unique();
            $table->decimal('retail_price', 15, 2);
            $table->decimal('wholesale_price', 15, 2)->nullable();
            $table->integer('min_wholesale_qty')->default(12); // পাইকারি সেলের কন্ডিশন
            $table->boolean('has_variation')->default(false); // ভেরিয়েশন আছে কি না
            $table->date('expiry_date')->nullable(); // ওষুধের জন্য
            $table->string('batch_number')->nullable(); // ফার্মার জন্য
            $table->boolean('is_active')->default(true);
            $table->integer('stock_qty')->default(0); // Base stock if no variation
            $table->string('main_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
