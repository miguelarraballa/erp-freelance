<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('factura_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('evento'); // created, updated, status_changed, emailed, pdf_generated, canceled, etc.
            $table->json('datos')->nullable(); // snapshot o diff
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();

            // Cadena de integridad (tamper-evident)
            $table->char('prev_hash', 64)->nullable();
            $table->char('hash', 64);

            $table->timestamp('created_at')->useCurrent();
        });

        // Triggers para hacer la tabla inmutable
        DB::unprepared("
            CREATE TRIGGER factura_logs_no_update
            BEFORE UPDATE ON factura_logs
            FOR EACH ROW
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'factura_logs es inmutable (no UPDATE)';
        ");

        DB::unprepared("
            CREATE TRIGGER factura_logs_no_delete
            BEFORE DELETE ON factura_logs
            FOR EACH ROW
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'factura_logs es inmutable (no DELETE)';
        ");
    }

    public function down(): void {
        DB::unprepared("DROP TRIGGER IF EXISTS factura_logs_no_update;");
        DB::unprepared("DROP TRIGGER IF EXISTS factura_logs_no_delete;");
        Schema::dropIfExists('factura_logs');
    }
};