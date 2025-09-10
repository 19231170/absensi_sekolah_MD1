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
        // Primeiro, precisamos obter os valores atuais para converter
        $kelas = \DB::table('kelas')->get(['id', 'tingkat']);
        
        // Mapear valores para backup
        $backup = [];
        foreach ($kelas as $kelas_item) {
            $backup[$kelas_item->id] = $kelas_item->tingkat;
        }
        
        // Alterar a coluna para inteiro
        Schema::table('kelas', function (Blueprint $table) {
            $table->string('tingkat_backup')->nullable();
        });
        
        // Backup dos valores antigos
        foreach ($backup as $id => $tingkat) {
            \DB::table('kelas')->where('id', $id)->update(['tingkat_backup' => $tingkat]);
        }
        
        // Alterar a coluna para inteiro
        Schema::table('kelas', function (Blueprint $table) {
            $table->string('tingkat')->nullable()->change();
        });
        
        // Converter e atualizar os valores
        foreach ($backup as $id => $tingkat) {
            $new_value = null;
            
            // Converter numerais romanos para inteiros
            switch ($tingkat) {
                case 'X':
                    $new_value = 10;
                    break;
                case 'XI':
                    $new_value = 11;
                    break;
                case 'XII':
                    $new_value = 12;
                    break;
                default:
                    // Tentar extrair um número se for um formato diferente
                    if (is_numeric($tingkat)) {
                        $new_value = (int) $tingkat;
                    }
            }
            
            \DB::table('kelas')->where('id', $id)->update(['tingkat' => $new_value]);
        }
        
        // Alterar o tipo da coluna para inteiro
        Schema::table('kelas', function (Blueprint $table) {
            $table->integer('tingkat')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar o tipo da coluna para string
        Schema::table('kelas', function (Blueprint $table) {
            $table->string('tingkat')->nullable()->change();
        });
        
        // Restaurar os valores originais
        $kelas = \DB::table('kelas')->whereNotNull('tingkat_backup')->get(['id', 'tingkat_backup']);
        
        foreach ($kelas as $kelas_item) {
            \DB::table('kelas')->where('id', $kelas_item->id)
                ->update(['tingkat' => $kelas_item->tingkat_backup]);
        }
        
        // Remover a coluna de backup
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('tingkat_backup');
        });
    }
};
