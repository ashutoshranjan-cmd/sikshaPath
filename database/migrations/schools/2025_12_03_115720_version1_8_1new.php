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
            // Step 1: Add the new date columns
            Schema::connection('school')->table('semesters', function (Blueprint $table) {
                $table->date('start_date')->nullable()->after('end_month');
                $table->date('end_date')->nullable()->after('start_date');
            });

            // Step 2: Migrate existing data - Convert start_month and end_month to dates
            // Get current year for date conversion
            $currentYear = date('Y');
            $nextYear = $currentYear + 1;

            // Check if semesters table exists and has the month columns
            if (
                Schema::connection('school')->hasTable('semesters') &&
                Schema::connection('school')->hasColumn('semesters', 'start_month')
            ) {

                // Get all semesters for this school database
                $semesters = DB::connection('school')->table('semesters')
                    ->whereNull('start_date')
                    ->whereNull('end_date')
                    ->whereNotNull('start_month')
                    ->whereNotNull('end_month')
                    ->get();

                foreach ($semesters as $semester) {
                    // Convert start_month to start_date (first day of the month)
                    $startDate = $currentYear . '-' . str_pad($semester->start_month, 2, '0', STR_PAD_LEFT) . '-01';

                    // Convert end_month to end_date (first day of the month)
                    // If end_month is less than start_month, it means it spans to next year
                    if ($semester->end_month < $semester->start_month) {
                        $endYear = $nextYear;
                    } else {
                        $endYear = $currentYear;
                    }
                    // Calculate the last day of the end_month for the proper end_date
                    $endMonthPadded = str_pad($semester->end_month, 2, '0', STR_PAD_LEFT);
                    $lastDay = date('t', strtotime($endYear . '-' . $endMonthPadded . '-01'));
                    $endDate = $endYear . '-' . $endMonthPadded . '-' . $lastDay;

                    // Update the semester with converted dates
                    DB::connection('school')->table('semesters')
                        ->where('id', $semester->id)
                        ->update([
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                        ]);
                }
            }

            // Step 3: Remove the old month columns
            Schema::connection('school')->table('semesters', function (Blueprint $table) {
                $table->dropColumn(['start_month', 'end_month']);
            });

            Schema::table('transportation_payments', function (Blueprint $table) {
                $table->boolean('include_amount')->nullable()->default(null)->after('amount');
            });

            Schema::table('staff_salaries', function (Blueprint $table) {
                $table->date('expiry_date')->nullable()->after('percentage');
            });


            Schema::table('staff_attendances', function (Blueprint $table) {
                // Add optional reason for the attendance
                if (!Schema::hasColumn('staff_attendances', 'reason')) {
                    $table->string('reason')->nullable()->after('type');
                }

                // Add leave_id (foreign key to leaves table)
                if (!Schema::hasColumn('staff_attendances', 'leave_id')) {
                    $table->unsignedBigInteger('leave_id')->nullable()->after('reason');

                    // Add FK constraint only if leaves table exists
                    if (Schema::hasTable('leaves')) {
                        $table->foreign('leave_id')
                            ->references('id')
                            ->on('leaves')
                            ->onDelete('set null');
                    }
                }

                // Update type column with clear meaning
                $table->tinyInteger('type')
                    ->comment('0=Absent, 1=Present, 3=Holiday, 4=First half present, 5=Second half present')
                    ->change();
            });

            Schema::table('notifications', function (Blueprint $table) {
                $table->boolean('is_custom')->default(0)->after('send_to')->comment('0 => Autogenerate , 1 => Custom');
            });

            Schema::create('user_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_id')->constrained('notifications')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });

            Schema::table('staff_attendances', function (Blueprint $table) {

                if (!Schema::hasColumn('staff_attendances', 'leave_detail_id')) {
                    $table->unsignedBigInteger('leave_detail_id')
                        ->nullable()
                        ->after('leave_id');

                    if (Schema::hasTable('leave_details')) {
                        $table->foreign('leave_detail_id')
                            ->references('id')
                            ->on('leave_details')
                            ->onDelete('set null');
                    }
                }
            });

            Schema::table('users', function (Blueprint $table) {
                $table->string('web_fcm', 1024)->nullable()->after('fcm_id');
            });

            Schema::create('trip_reports', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('route_vehicle_history_id');
                $table->unsignedBigInteger('pickup_point_id')->nullable();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();

                $table->timestamps();

                // Foreign Keys (optional but recommended)
                $table->foreign('route_vehicle_history_id')
                    ->references('id')
                    ->on('route_vehicle_histories')
                    ->onDelete('cascade');

                $table->foreign('pickup_point_id')
                    ->references('id')
                    ->on('pickup_points')
                    ->onDelete('cascade');

                $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });

            // Add class_id column to online_exam_questions table (nullable first for migration)
            if (!Schema::hasColumn('online_exam_questions', 'class_id')) {
                Schema::table('online_exam_questions', function (Blueprint $table) {
                    $table->foreignId('class_id')->nullable()->after('id')->constrained('classes')->onDelete('cascade');
                });
            }

            // Add class_subject_id column to online_exam_questions table (nullable first for migration)
            if (!Schema::hasColumn('online_exam_questions', 'class_subject_id')) {
                Schema::table('online_exam_questions', function (Blueprint $table) {
                    $table->foreignId('class_subject_id')->nullable(true)->after('class_id')->constrained('class_subjects')->onDelete('cascade');
                });
            }

            // Migrate data: Get class_id from class_section_id in online_exam_questions table
            // Only if class_section_id column still exists (it may have been dropped in a previous migration)
            if (Schema::hasColumn('online_exam_questions', 'class_section_id')) {
                DB::statement('
                UPDATE online_exam_questions oeq
                INNER JOIN class_sections cs ON oeq.class_section_id = cs.id
                SET oeq.class_id = cs.class_id
                WHERE oeq.class_section_id IS NOT NULL AND oeq.class_id IS NULL
            ');
            }

            // Note: class_subject_id was already dropped in a previous migration (2025_07_28_123953_version1_7_0.php)
            // So we'll migrate it from online_exam_question_commons table below

            // Also migrate from online_exam_question_commons if class_id is still null
            // Get the first class_id from commons for each question
            DB::statement('
            UPDATE online_exam_questions oeq
            INNER JOIN (
                SELECT oeqc.online_exam_question_id, cs.class_id
                FROM online_exam_question_commons oeqc
                INNER JOIN class_sections cs ON oeqc.class_section_id = cs.id
                WHERE oeqc.online_exam_question_id IN (
                    SELECT id FROM online_exam_questions WHERE class_id IS NULL
                )
                GROUP BY oeqc.online_exam_question_id, cs.class_id
                LIMIT 1000000
            ) first_class ON oeq.id = first_class.online_exam_question_id
            SET oeq.class_id = first_class.class_id
            WHERE oeq.class_id IS NULL
        ');

            // Migrate class_subject_id from online_exam_question_commons if class_subject_id is still null
            // Get the first class_subject_id from commons for each question
            DB::statement('
            UPDATE online_exam_questions oeq
            INNER JOIN (
                SELECT oeqc.online_exam_question_id, oeqc.class_subject_id
                FROM online_exam_question_commons oeqc
                WHERE oeqc.class_subject_id IS NOT NULL
                AND oeqc.online_exam_question_id IN (
                    SELECT id FROM online_exam_questions WHERE class_subject_id IS NULL
                )
                GROUP BY oeqc.online_exam_question_id, oeqc.class_subject_id
                LIMIT 1000000
            ) first_subject ON oeq.id = first_subject.online_exam_question_id
            SET oeq.class_subject_id = first_subject.class_subject_id
            WHERE oeq.class_subject_id IS NULL
        ');

            // Make class_id not nullable after migration
            Schema::table('online_exam_questions', function (Blueprint $table) {
                $table->foreignId('class_id')->nullable(true)->change();
            });

            // Make class_subject_id not nullable after migration
            Schema::table('online_exam_questions', function (Blueprint $table) {
                $table->foreignId('class_subject_id')->nullable(true)->change();
            });

            // Drop foreign key constraint and column for class_section_id if it exists
            if (Schema::hasColumn('online_exam_questions', 'class_section_id')) {
                Schema::table('online_exam_questions', function (Blueprint $table) {
                    // Drop foreign key first
                    $table->dropForeign(['class_section_id']);
                    // Then drop the column
                    $table->dropColumn('class_section_id');
                });
            }

            Schema::dropIfExists('online_exam_question_commons');

            Schema::table('transportation_payments', function (Blueprint $table) {
                $table->decimal('included_amount')
                    ->default(0)
                      ->after('include_amount');
            });

        } catch (\Exception $e) {
            
            \Log::error('Error in migration: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add month columns first
        Schema::connection('school')->table('semesters', function (Blueprint $table) {
            $table->tinyInteger('start_month')->nullable()->after('name');
            $table->tinyInteger('end_month')->nullable()->after('start_month');
        });

        // Migrate dates back to months (if needed)
        if (
            Schema::connection('school')->hasTable('semesters') &&
            Schema::connection('school')->hasColumn('semesters', 'start_date')
        ) {

            $semesters = DB::connection('school')->table('semesters')
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->get();

            foreach ($semesters as $semester) {
                $startMonth = date('n', strtotime($semester->start_date)); // 'n' gives month without leading zero (1-12)
                $endMonth = date('n', strtotime($semester->end_date));

                DB::connection('school')->table('semesters')
                    ->where('id', $semester->id)
                    ->update([
                        'start_month' => $startMonth,
                        'end_month' => $endMonth,
                    ]);
            }
        }

        // Drop date columns
        Schema::connection('school')->table('semesters', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });

        Schema::table('transportation_payments', function (Blueprint $table) {
            $table->dropColumn('include_amount');
        });

        Schema::table('staff_salaries', function (Blueprint $table) {
            $table->dropColumn('expiry_date');
        });

        Schema::table('staff_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('staff_attendances', 'leave_id')) {
                $table->dropForeign(['leave_id']);
                $table->dropColumn('leave_id');
            }

            if (Schema::hasColumn('staff_attendances', 'reason')) {
                $table->dropColumn('reason');
            }

            $table->tinyInteger('type')->comment('0=Absent, 1=Present, 3=Holiday')->change(); // remove comment
        });

        Schema::dropIfExists('user_notifications');
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('is_custom');
        });

        Schema::table('staff_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('staff_attendances', 'leave_detail_id')) {
                $table->dropForeign(['leave_detail_id']);
                $table->dropColumn('leave_detail_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('web_fcm');
        });


        Schema::dropIfExists('trip_reports');

        // Add class_section_id column back
        if (!Schema::hasColumn('online_exam_questions', 'class_section_id')) {
            Schema::table('online_exam_questions', function (Blueprint $table) {
                $table->foreignId('class_section_id')->nullable()->after('id')->constrained('class_sections')->onDelete('cascade');
            });
        }

        // Migrate data back: Get first class_section_id for each class_id
        // Note: This is a simplified rollback - we'll use the first section of each class
        DB::statement('
            UPDATE online_exam_questions oeq
            INNER JOIN (
                SELECT cs.id as class_section_id, cs.class_id
                FROM class_sections cs
                INNER JOIN (
                    SELECT class_id, MIN(id) as min_id
                    FROM class_sections
                    GROUP BY class_id
                ) first_section ON cs.id = first_section.min_id
            ) first_sections ON oeq.class_id = first_sections.class_id
            SET oeq.class_section_id = first_sections.class_section_id
            WHERE oeq.class_id IS NOT NULL AND oeq.class_section_id IS NULL
        ');


        // Make class_section_id not nullable
        Schema::table('online_exam_questions', function (Blueprint $table) {
            $table->foreignId('class_section_id')->nullable(false)->change();
        });

        // Drop class_id column
        if (Schema::hasColumn('online_exam_questions', 'class_id')) {
            Schema::table('online_exam_questions', function (Blueprint $table) {
                $table->dropForeign(['class_id']);
                $table->dropColumn('class_id');
            });
        }

        // Drop class_subject_id column (we added it in up(), so we drop it in down())
        if (Schema::hasColumn('online_exam_questions', 'class_subject_id')) {
            Schema::table('online_exam_questions', function (Blueprint $table) {
                $table->dropForeign(['class_subject_id']);
                $table->dropColumn('class_subject_id');
            });
        }

        if (!Schema::hasTable('online_exam_question_commons')) {
            Schema::create('online_exam_question_commons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('online_exam_question_id')->constrained('online_exam_questions')->onDelete('cascade');
                $table->foreignId('class_section_id')->constrained('class_sections')->onDelete('cascade');
                $table->foreignId('class_subject_id')->nullable()->constrained('class_subjects')->onDelete('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('transportation_payments', function (Blueprint $table) {
            $table->dropColumn('included_amount');
        });
    }
};
