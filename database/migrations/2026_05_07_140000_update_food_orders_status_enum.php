<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $orders = DB::table('food_orders')->get()->map(function ($order) {
            return [
                'user_id' => $order->user_id,
                'reservation_id' => $order->reservation_id ?? null,
                'items' => $order->items,
                'total' => $order->total,
                'notes' => $order->notes ?? null,
                'status' => $order->status ?? 'pending',
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];
        })->toArray();

        Schema::dropIfExists('food_orders');

        Schema::create('food_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->json('items');
            $table->decimal('total', 10, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'preparing', 'delivered', 'rejected', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        if (!empty($orders)) {
            DB::table('food_orders')->insert($orders);
        }
    }

    public function down(): void
    {
        $orders = DB::table('food_orders')->get()->map(function ($order) {
            return [
                'user_id' => $order->user_id,
                'reservation_id' => $order->reservation_id ?? null,
                'items' => $order->items,
                'total' => $order->total,
                'notes' => $order->notes ?? null,
                'status' => in_array($order->status, ['pending', 'preparing', 'delivered', 'cancelled']) ? $order->status : 'pending',
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];
        })->toArray();

        Schema::dropIfExists('food_orders');

        Schema::create('food_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->json('items');
            $table->decimal('total', 10, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'preparing', 'delivered', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        if (!empty($orders)) {
            DB::table('food_orders')->insert($orders);
        }
    }
};
