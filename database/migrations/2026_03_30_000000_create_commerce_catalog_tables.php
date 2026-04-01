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
        Schema::create('child_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('display_name');
            $table->unsignedInteger('birth_year')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id', 'idx_child_profiles_user');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku')->unique();
            $table->enum('type', ['adventure', 'mission', 'bundle', 'subscription']);
            $table->json('title');
            $table->json('description')->nullable();
            $table->foreignUuid('parent_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('parent_product_id', 'idx_products_parent');
            $table->index('sku', 'idx_products_sku');
        });

        DB::statement(
            "ALTER TABLE products ADD CONSTRAINT chk_mission_has_parent CHECK ((type = 'mission' AND parent_product_id IS NOT NULL) OR type <> 'mission')"
        );

        Schema::create('content_packs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->string('pack_key');
            $table->string('version');
            $table->string('min_app_version')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['product_id', 'pack_key', 'version']);
        });

        Schema::create('pack_artifacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('content_pack_id')->constrained()->cascadeOnDelete();
            $table->text('file_url');
            $table->unsignedBigInteger('bytes')->nullable();
            $table->string('sha256_hex', 64);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('content_pack_id', 'idx_pack_artifacts_pack');
        });

        Schema::create('product_offers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['apple', 'google', 'stripe', 'manual']);
            $table->string('store_product_id');
            $table->char('currency', 3)->default('USD');
            $table->integer('amount_minor')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unique(['platform', 'store_product_id']);
            $table->index('product_id', 'idx_offers_product');
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained();
            $table->foreignUuid('offer_id')->nullable()->constrained('product_offers')->nullOnDelete();
            $table->enum('platform', ['apple', 'google', 'stripe', 'manual']);
            $table->string('store_transaction_id');
            $table->longText('raw_receipt')->nullable();
            $table->timestamp('verified_at')->useCurrent();
            $table->string('status')->default('completed');

            $table->unique(['platform', 'store_transaction_id']);
            $table->index('user_id', 'idx_purchases_user');
        });

        Schema::create('entitlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->string('source');
            $table->foreignUuid('source_ref')->nullable()->constrained('purchases')->nullOnDelete();
            $table->string('source_ref_key', 36)->storedAs("ifnull(`source_ref`, '')");
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'product_id', 'source', 'source_ref_key'], 'entitlements_unique_source');
            $table->index('user_id', 'idx_entitlements_user');
            $table->index('product_id', 'idx_entitlements_product');
        });

        Schema::create('catalog_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version');
            $table->json('payload');
            $table->timestamp('published_at')->useCurrent();
        });

        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('device_id');
            $table->timestamp('last_seen_at')->useCurrent();

            $table->unique(['user_id', 'platform', 'device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
        Schema::dropIfExists('refresh_tokens');
        Schema::dropIfExists('catalog_snapshots');
        Schema::dropIfExists('entitlements');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('product_offers');
        Schema::dropIfExists('pack_artifacts');
        Schema::dropIfExists('content_packs');
        Schema::dropIfExists('products');
        Schema::dropIfExists('child_profiles');
    }
};
