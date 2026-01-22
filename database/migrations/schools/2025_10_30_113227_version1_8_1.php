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
         // Create transportation_attendance table
         Schema::create('transportation_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('pickup_point_id')->nullable()->constrained('pickup_points')->cascadeOnDelete();
            $table->foreignId('route_vehicle_id')->nullable()->constrained('route_vehicles')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->date('date');
            $table->enum('status', ['present', 'absent']);
            $table->unsignedTinyInteger('pickup_drop')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Foreign key for trip_id referencing route_vehicle_histories
            $table->foreign('trip_id')
                ->references('id')
                ->on('route_vehicle_histories')
                ->onDelete('set null');

            // Note: unique constraint on (user_id, date, shift_id) is intentionally not included
            // as it was removed in a later migration to allow more flexible attendance tracking

            // Indexes for performance
            $table->index(['date', 'shift_id']);
            $table->index(['route_vehicle_id', 'pickup_point_id']);
        });

        // Add type column to payment_transactions table
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('type')->nullable()->default("fees")->after('payment_status');
        });

        // Add license column to staffs table (moved from users)
        Schema::table('staffs', function (Blueprint $table) {
            $table->string('license', 512)->nullable()->after('salary');
        });

        // Add vehicle_id and file columns to expenses table
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->string('file')->nullable()->after('amount');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
        });

        // Create staff_attendances table
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->comment('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('session_year_id')->constrained('session_years')->onDelete('cascade');
            $table->tinyInteger('type')->comment('0=Absent, 1=Present, 3=Holiday');
            $table->date('date');
            $table->string('remark', 512)->nullable();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->timestamps();
        });

        // Make section_id nullable in class_sections table
        Schema::table('class_sections', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->unsignedBigInteger('section_id')->nullable()->change();
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
        });

        // Add title column to diaries table
        if (Schema::hasTable('diaries')) {
            Schema::table('diaries', function (Blueprint $table) {
                $table->string('title')->nullable()->after('diary_category_id');
            });
        }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('payment_transactions', 'type')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        if (Schema::hasColumn('staffs', 'license')) {
            Schema::table('staffs', function (Blueprint $table) {
                $table->dropColumn('license');
            });
        }

        if (Schema::hasColumn('expenses', 'vehicle_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropForeign(['vehicle_id']);
                $table->dropColumn('vehicle_id');
            });
        }

        if (Schema::hasColumn('expenses', 'file')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('file');
            });
        }

        if (Schema::hasColumn('expenses', 'created_by')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            });
        }

        if (Schema::hasColumn('class_sections', 'section_id')) {
            Schema::table('class_sections', function (Blueprint $table) {
                $table->dropForeign(['section_id']);
                $table->unsignedBigInteger('section_id')->nullable(false)->change();
                $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            });
        }

        // Remove title column from diaries table
        if (Schema::hasTable('diaries') && Schema::hasColumn('diaries', 'title')) {
            Schema::table('diaries', function (Blueprint $table) {
                $table->dropColumn('title');
            });
        }

        Schema::dropIfExists('transportation_attendance');
    }
};
