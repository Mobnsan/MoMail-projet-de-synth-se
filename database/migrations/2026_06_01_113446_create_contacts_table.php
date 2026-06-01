<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ownerId')->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('group')->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('contacts');
    }
};
