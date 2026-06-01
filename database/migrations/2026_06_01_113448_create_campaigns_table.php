<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ownerId')->index();
            $table->string('name');
            $table->uuid('templateId')->index();
            $table->string('status')->default('pending');
            $table->timestamp('scheduledAt')->nullable();
            $table->integer('recipientCount')->default(0);
            $table->integer('deliveredCount')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('campaigns');
    }
};
