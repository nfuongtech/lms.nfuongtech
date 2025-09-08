    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('chuong_trinh_chuyen_de', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chuong_trinh_id')->constrained()->cascadeOnDelete();
                $table->foreignId('chuyen_de_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('chuong_trinh_chuyen_de');
        }
    };
