<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('reviews', function (Blueprint $table) {
        $table->id(); // Khóa chính
        $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Khóa ngoại đến bảng products
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Khóa ngoại đến bảng users
        $table->unsignedTinyInteger('rating'); // Đánh giá sản phẩm (1-5 sao)
        $table->text('comment')->nullable(); // Bình luận của người dùng
        $table->timestamps(); // Thời gian tạo và cập nhật
    });
}

public function down()
{
    Schema::dropIfExists('reviews');
}
};
