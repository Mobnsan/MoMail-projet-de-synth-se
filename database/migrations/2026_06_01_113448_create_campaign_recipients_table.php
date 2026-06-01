<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id')->index();
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('company')->nullable();
            $table->string('status')->nullable();
            $table->string('subject')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('campaign_recipients');
    }
};
