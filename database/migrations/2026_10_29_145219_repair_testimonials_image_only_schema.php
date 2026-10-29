<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Force testimonials to the image-only shape. Shared hosts sometimes
     * recorded the earlier simplify migration without actually dropping
     * quote/name, which makes Livewire saves 500 with "Field quote doesn't
     * have a default value".
     */
    public function up(): void
    {
        if (! Schema::hasTable('testimonials')) {
            Schema::create('testimonials', function (Blueprint $table) {
                $table->id();
                $table->string('image');
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->string('status')->default('draft')->index();
                $table->timestamps();
            });

            return;
        }

        $legacy = Schema::hasColumn('testimonials', 'quote')
            || Schema::hasColumn('testimonials', 'name')
            || Schema::hasColumn('testimonials', 'role')
            || Schema::hasColumn('testimonials', 'source');

        if (! $legacy && Schema::hasColumn('testimonials', 'image')) {
            return;
        }

        Schema::create('testimonials_repair', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        $rows = DB::table('testimonials')->orderBy('id')->get();

        foreach ($rows as $row) {
            $image = $row->image ?? null;

            if (! is_string($image) || $image === '') {
                continue;
            }

            DB::table('testimonials_repair')->insert([
                'id' => $row->id,
                'image' => $image,
                'sort_order' => (int) ($row->sort_order ?? 0),
                'status' => (string) ($row->status ?? 'draft'),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop('testimonials');
        Schema::rename('testimonials_repair', 'testimonials');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible repair — legacy text columns are intentionally gone.
    }
};
