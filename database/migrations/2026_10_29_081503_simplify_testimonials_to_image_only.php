<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Testimonials are full Canva card images — no quote/name text fields.
     * Skips when the create migration already built the slim table.
     */
    public function up(): void
    {
        if (! Schema::hasTable('testimonials') || ! Schema::hasColumn('testimonials', 'quote')) {
            return;
        }

        DB::table('testimonials')->whereNull('image')->orWhere('image', '')->delete();

        Schema::create('testimonials_tmp', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        $rows = DB::table('testimonials')
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->orderBy('id')
            ->get(['id', 'image', 'sort_order', 'status', 'created_at', 'updated_at']);

        foreach ($rows as $row) {
            DB::table('testimonials_tmp')->insert([
                'id' => $row->id,
                'image' => $row->image,
                'sort_order' => $row->sort_order,
                'status' => $row->status,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop('testimonials');
        Schema::rename('testimonials_tmp', 'testimonials');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('testimonials') || Schema::hasColumn('testimonials', 'quote')) {
            return;
        }

        Schema::create('testimonials_tmp', function (Blueprint $table) {
            $table->id();
            $table->text('quote')->nullable();
            $table->string('name')->nullable();
            $table->string('role')->nullable();
            $table->string('source')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        $rows = DB::table('testimonials')->orderBy('id')->get();

        foreach ($rows as $row) {
            DB::table('testimonials_tmp')->insert([
                'id' => $row->id,
                'quote' => '',
                'name' => 'Card #'.$row->sort_order,
                'role' => null,
                'source' => null,
                'image' => $row->image,
                'sort_order' => $row->sort_order,
                'status' => $row->status,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop('testimonials');
        Schema::rename('testimonials_tmp', 'testimonials');
    }
};
