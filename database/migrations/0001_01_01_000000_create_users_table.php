<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Instituciones cliente (bancos, entidades). Se crea antes que users por la FK.
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('legal_name')->nullable();     // Razón social para facturación
            $table->string('tax_id', 40)->nullable();      // NIT
            $table->string('billing_email')->nullable();
            $table->string('billing_address')->nullable();
            $table->unsignedInteger('alliance_points')->default(0);
            $table->foreignId('account_manager_id')->nullable(); // FK a users, se agrega abajo
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            // admin = staff Blockfinity con acceso al CMS
            // client_admin = administrador de cuenta de la institución
            // client = funcionario de la institución
            $table->string('role', 20)->default('client')->index();
            $table->string('area')->nullable();        // Gerencia de Riesgos, Cumplimiento...
            $table->string('position')->nullable();    // Cargo / rol visible (ej. Client Success)
            $table->string('phone', 40)->nullable();
            $table->string('booking_url')->nullable(); // Calendly / Google Appointment de la ejecutiva
            // Credencial digital (fase 1: tarjeta + QR; fase 3: NFT)
            $table->string('credential_number', 20)->nullable()->unique();
            $table->uuid('credential_code')->nullable()->unique();
            $table->timestamp('credential_issued_at')->nullable();
            $table->string('wallet_address', 64)->nullable(); // reservado para fase 3
            $table->string('token_id', 80)->nullable();        // reservado para fase 3
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('institutions', function (Blueprint $table) {
            $table->foreign('account_manager_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropForeign(['account_manager_id']);
        });
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('institutions');
    }
};
