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
            Schema::table('chuyen_des', function (Blueprint $table) {
                $table->string('trang_thai_tai_lieu')->nullable();
                $table->string('bai_giang_path')->nullable();
            });
        }


        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('chuyen_des', function (Blueprint $table) {
                $table->dropColumn(['trang_thai_tai_lieu', 'bai_giang_path']);
            });
        }
    };
