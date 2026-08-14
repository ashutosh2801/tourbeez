<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_gallery_uploads', function (Blueprint $table) {
            $table->id();

            /*
             * Existing uploads table ka record.
             */
            $table->unsignedBigInteger('upload_id');

            /*
             * Kis booking se image upload hui.
             */
            $table->unsignedBigInteger('order_id');

            /*
             * Image kis tour gallery me show hogi.
             */
            $table->unsignedBigInteger('tour_id');

            $table->string('uploaded_by_type', 30)
                ->default('passenger');

            $table->boolean('is_approved')
                ->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('upload_id')
                ->references('id')
                ->on('uploads')
                ->cascadeOnDelete();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

            $table->index([
                'tour_id',
                'order_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_gallery_uploads');
    }
};