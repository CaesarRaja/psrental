<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            if (!Schema::hasColumn('foods', 'photo')) {
                $table->string('photo')->nullable()->after('emoji');
            }
            if (!Schema::hasColumn('foods', 'status')) {
                $table->enum('status', ['available', 'unavailable'])->default('available')->after('stock');
            }
            $table->string('emoji')->nullable()->change();
        });

        Schema::table('food_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('food_orders', 'reservation_id')) {
                $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn('photo');
            $table->dropColumn('status');
        });

        Schema::table('food_orders', function (Blueprint $table) {
            $table->dropForeign(['reservation_id']);
            $table->dropColumn('reservation_id');
        });
    }
};
