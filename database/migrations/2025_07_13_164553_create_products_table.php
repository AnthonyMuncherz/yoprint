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
            $table->string('unique_key')->unique();
            $table->text('product_title');
            $table->text('product_description')->nullable();
            $table->string('style_number')->nullable();
            $table->string('sanmar_mainframe_color')->nullable();
            $table->string('size')->nullable();
            $table->string('color_name')->nullable();
            $table->decimal('piece_price', 10, 2)->nullable();
            $table->unsignedBigInteger('file_upload_id')->nullable();
            $table->timestamps();
            
            $table->foreign('file_upload_id')->references('id')->on('file_uploads')->onDelete('set null');
            $table->index(['unique_key']);
            $table->index(['file_upload_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
