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
        try {

            // Create vehicles table
            Schema::create('vehicles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('vehicle_number');
                $table->integer('capacity');
                $table->tinyInteger('status'); // 0 = inactive, 1 = active
                $table->softDeletes();
                $table->timestamps();
            });


            // Create routes table with shift_id included from the start
            Schema::create('routes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('distance', 8, 2)->nullable()->comment('Distance in kilometers');
                $table->tinyInteger('status')->default(1);
                $table->foreignId('shift_id')->nullable()->constrained('shifts')->onUpdate('cascade')->onDelete('set null');
                $table->timestamps();
            });

            // Create pickup_points table
            Schema::create('pickup_points', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });

            // Create route_pickup_points table (junction table)
            Schema::create('route_pickup_points', function (Blueprint $table) {
                $table->id();
                $table->foreignId('route_id')->constrained('routes')->onDelete('cascade');
                $table->foreignId('pickup_point_id')->constrained('pickup_points')->onDelete('cascade');
                $table->time('pickup_time')->nullable();
                $table->time('drop_time')->nullable();
                $table->integer('order')->default(1)->comment('Order of pickup point in route');
                $table->timestamps();

                // Ensure unique combination of route and pickup point
                $table->unique(['route_id', 'pickup_point_id']);
            });

            // Create transportation_fees table
            Schema::create('transportation_fees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pickup_point_id')->constrained('pickup_points')->onDelete('cascade');
                $table->string('duration');
                $table->decimal('fee_amount', 10, 2);
                $table->timestamps();

                // Ensure unique combination of pickup point and duration
                $table->unique(['pickup_point_id', 'duration']);
            });

            // Create route_vehicles table with final structure (no shift_id, no pickup_point_id, no bus_number)
            Schema::create('route_vehicles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('route_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
                $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('helper_id')->nullable()->constrained('users')->nullOnDelete();
                $table->time('pickup_start_time')->nullable();
                $table->time('pickup_end_time')->nullable();
                $table->time('drop_start_time')->nullable();
                $table->time('drop_end_time')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->softDeletes();
                $table->timestamps();
            });

            // Create route_vehicle_histories table with final structure
            Schema::create('route_vehicle_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('route_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
                $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('helper_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
                $table->foreignId('last_pickup_point_id')->nullable()->constrained('pickup_points')->onDelete('set null');
                $table->enum('type', ['pickup', 'drop'])->default('pickup');
                $table->time('start_time')->nullable();
                $table->time('actual_start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->time('actual_end_time')->nullable();
                $table->date('date');
                $table->enum('status', ['upcoming', 'inprogress', 'completed'])->default('upcoming');
                $table->foreignId('session_year_id')->constrained('session_years')->cascadeOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });

            // Create user_assign_vehicles table
            Schema::create('user_assign_vehicles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('route_id')->constrained()->cascadeOnDelete();
                $table->foreignId('pickup_point_id')->constrained()->cascadeOnDelete();
                $table->foreignId('route_vehicle_id')->constrained('route_vehicles')->cascadeOnDelete();
                $table->foreignId('session_year_id')->constrained('session_years')->cascadeOnDelete();
                $table->timestamp('assigned_at')->nullable();
                $table->timestamps();
            });

            // Create transportation_payments table with all final columns
            Schema::create('transportation_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('route_vehicle_id')->nullable()->constrained('route_vehicles')->cascadeOnDelete();
                $table->foreignId('pickup_point_id')->nullable()->constrained('pickup_points')->cascadeOnDelete();
                $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
                $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->cascadeOnDelete();
                $table->foreignId('transportation_fee_id')->nullable()->constrained('transportation_fees')->cascadeOnDelete();
                $table->decimal('amount', 10, 2)->nullable();
                $table->enum('status', ['pending', 'paid', 'cancelled']);
                $table->timestamp('paid_at')->nullable();
                $table->date('expiry_date')->nullable();
                $table->foreignId('session_year_id')->constrained('session_years')->cascadeOnDelete();
                $table->timestamps();
            });

            // Create transportation_requests table
            Schema::create('transportation_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('transportation_fee_id')->constrained('transportation_fees')->cascadeOnDelete();
                $table->foreignId('pickup_point_id')->constrained('pickup_points')->cascadeOnDelete();
                $table->foreignId('session_year_id')->constrained('session_years')->cascadeOnDelete();
                $table->enum('status', ['pending', 'approved', 'rejected']);
                $table->timestamps();
            });

       } catch (\Throwable $th) {
            // throw $th;
       }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {

            // Drop tables in reverse order
            Schema::dropIfExists('staff_attendances');
            Schema::dropIfExists('transportation_attendance');
            Schema::dropIfExists('transportation_requests');
            Schema::dropIfExists('transportation_payments');
            Schema::dropIfExists('user_assign_vehicles');
            Schema::dropIfExists('route_vehicle_histories');
            Schema::dropIfExists('route_vehicles');
            Schema::dropIfExists('transportation_fees');
            Schema::dropIfExists('route_pickup_points');
            Schema::dropIfExists('pickup_points');
            Schema::dropIfExists('routes');
            Schema::dropIfExists('vehicles');

            // Revert table modifications
           

            // Remove trip_id from transportation_attendance table
            if (Schema::hasTable('transportation_attendance')) {
                Schema::table('transportation_attendance', function (Blueprint $table) {
                    $table->dropForeign(['trip_id']);
                    $table->dropColumn('trip_id');
                });
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
};
