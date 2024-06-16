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
            Schema::create('work_schedules', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->foreignId('store_id')->constrained();
                $table->foreignId('work_shift_id')->constrained();
                $table->foreignId('employee_id')->constrained();
                $table->boolean('status');
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('work_schedules');
        }
    };
