<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'guru'])->default('guru')->after('email');
            $table->string('pin', 4)->nullable()->after('role');
            $table->string('qr_code')->unique()->nullable()->after('pin');
            $table->string('nip')->unique()->nullable()->after('qr_code'); // Nomor Induk Pegawai
            $table->string('mata_pelajaran')->nullable()->after('nip');
            $table->boolean('is_active')->default(true)->after('mata_pelajaran');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 
                'pin', 
                'qr_code', 
                'nip', 
                'mata_pelajaran', 
                'is_active', 
                'last_login_at'
            ]);
        });
    }
};
