<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data-only: photos are converted again with the improved converter
 * (same bag size/position on plain backgrounds). The background job
 * `valletta:normalize-images` picks them up on deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('product_images')->update(['normalized' => false]);
    }

    public function down(): void
    {
        // Nothing to undo.
    }
};
