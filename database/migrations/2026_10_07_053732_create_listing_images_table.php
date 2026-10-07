<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('listing_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('listings')->whereNotNull('image_path')->orderBy('id')->chunkById(100, function ($listings) {
            foreach ($listings as $listing) {
                DB::table('listing_images')->insert([
                    'listing_id' => $listing->id,
                    'image_path' => $listing->image_path,
                    'is_primary' => true,
                    'sort_order' => 0,
                    'created_at' => $listing->created_at,
                    'updated_at' => $listing->updated_at,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_images');
    }
};
