<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('email_logs', 'email_account_id')) {
                $table->foreignId('email_account_id')->nullable()->constrained('email_accounts')->nullOnDelete()->after('id');
            }
            if (! Schema::hasColumn('email_logs', 'subject')) {
                $table->string('subject')->nullable()->after('recipient_email');
            }
            if (! Schema::hasColumn('email_logs', 'content')) {
                $table->longText('content')->nullable()->after('subject');
            }
            if (! Schema::hasColumn('email_logs', 'error_message')) {
                $table->text('error_message')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            if (Schema::hasColumn('email_logs', 'email_account_id')) {
                // drop foreign key then column
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $sm->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
                $table->dropConstrainedForeignId('email_account_id');
            }
            if (Schema::hasColumn('email_logs', 'subject')) {
                $table->dropColumn('subject');
            }
            if (Schema::hasColumn('email_logs', 'content')) {
                $table->dropColumn('content');
            }
            if (Schema::hasColumn('email_logs', 'error_message')) {
                $table->dropColumn('error_message');
            }
        });
    }
};
