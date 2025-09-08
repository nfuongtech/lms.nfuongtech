    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('chuong_trinhs', function (Blueprint $table) {
                $table->id();
                $table->string('ma_chuong_trinh')->unique();
                $table->string('ten_chuong_trinh');
                $table->decimal('thoi_luong', 8, 2)->default(0);
                $table->text('muc_tieu_dao_tao')->nullable();
                $table->string('loai_hinh_dao_tao')->nullable();
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('chuong_trinhs');
        }
    };
    
