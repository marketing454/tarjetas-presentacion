<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        $usedSlugs = [];

        foreach (DB::table('branches')->orderBy('id')->get(['id', 'name']) as $branch) {
            $base = Str::slug($branch->name);
            $slug = $base;
            $i = 1;

            while (in_array($slug, $usedSlugs, true)) {
                $slug = "{$base}-{$i}";
                $i++;
            }

            $usedSlugs[] = $slug;

            DB::table('branches')->where('id', $branch->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
