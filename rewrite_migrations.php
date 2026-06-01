<?php

$migrationsPath = __DIR__ . '/database/migrations/';

$usersTable = <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->string('name');
            \$table->string('email')->unique();
            \$table->string('password_hash');
            \$table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint \$table) {
            \$table->string('email')->primary();
            \$table->string('token');
            \$table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint \$table) {
            \$table->string('id')->primary();
            \$table->foreignId('user_id')->nullable()->index();
            \$table->string('ip_address', 45)->nullable();
            \$table->text('user_agent')->nullable();
            \$table->longText('payload');
            \$table->integer('last_activity')->index();
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
PHP;

$contactsTable = <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contacts', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->uuid('ownerId')->index();
            \$table->string('name')->nullable();
            \$table->string('email')->nullable();
            \$table->string('company')->nullable();
            \$table->string('group')->nullable();
            \$table->json('extra_fields')->nullable();
            \$table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('contacts');
    }
};
PHP;

$templatesTable = <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('templates', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->uuid('ownerId')->index();
            \$table->string('name');
            \$table->string('senderName')->nullable();
            \$table->string('senderEmail')->nullable();
            \$table->string('subject');
            \$table->text('body');
            \$table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('templates');
    }
};
PHP;

$campaignsTable = <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campaigns', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->uuid('ownerId')->index();
            \$table->string('name');
            \$table->uuid('templateId')->index();
            \$table->string('status')->default('pending');
            \$table->timestamp('scheduledAt')->nullable();
            \$table->integer('recipientCount')->default(0);
            \$table->integer('deliveredCount')->default(0);
            \$table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('campaigns');
    }
};
PHP;

$recipientsTable = <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campaign_recipients', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->uuid('campaign_id')->index();
            \$table->string('email')->nullable();
            \$table->string('name')->nullable();
            \$table->string('company')->nullable();
            \$table->string('status')->nullable();
            \$table->string('subject')->nullable();
            \$table->text('error')->nullable();
            \$table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('campaign_recipients');
    }
};
PHP;

$settingsTable = <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('settings', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->uuid('ownerId')->index();
            \$table->string('provider')->nullable();
            \$table->json('providerConfig')->nullable();
            \$table->string('fromEmail')->nullable();
            \$table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('settings');
    }
};
PHP;

\$files = scandir(\$migrationsPath);
foreach(\$files as \$file) {
    if (strpos(\$file, 'create_users_table') !== false) file_put_contents(\$migrationsPath . \$file, \$usersTable);
    if (strpos(\$file, 'create_contacts_table') !== false) file_put_contents(\$migrationsPath . \$file, \$contactsTable);
    if (strpos(\$file, 'create_templates_table') !== false) file_put_contents(\$migrationsPath . \$file, \$templatesTable);
    if (strpos(\$file, 'create_campaigns_table') !== false) file_put_contents(\$migrationsPath . \$file, \$campaignsTable);
    if (strpos(\$file, 'create_campaign_recipients_table') !== false) file_put_contents(\$migrationsPath . \$file, \$recipientsTable);
    if (strpos(\$file, 'create_settings_table') !== false) file_put_contents(\$migrationsPath . \$file, \$settingsTable);
}

echo "Migrations updated.\n";
