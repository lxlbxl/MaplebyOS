<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('tasks', 'due_at')) {
            Schema::table('tasks', function (Blueprint $table) {
                // Check individually to be safe if partial run happened
                if (!Schema::hasColumn('tasks', 'completed_by')) {
                    $table->foreignId('completed_by')->nullable()->constrained('users');
                }
                if (!Schema::hasColumn('tasks', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'due_at')) {
                    $table->dateTime('due_at')->nullable();
                }
            });
        }

        // Migrate data
        // \DB::statement("UPDATE tasks SET due_at = due_date WHERE due_date IS NOT NULL");

        if (Schema::hasColumn('tasks', 'due_date')) {
            if (DB::getDriverName() === 'sqlite') {
                // Drop indexes safely using raw SQL for SQLite support
                \DB::statement("DROP INDEX IF EXISTS tasks_due_date_index");
                \DB::statement("DROP INDEX IF EXISTS tasks_status_due_date_index");
            } else {
                Schema::table('tasks', function (Blueprint $table) {
                    // Standard Laravel way for MySQL/Postgres
                    try {
                        $table->dropIndex(['due_date']);
                    } catch (\Exception $e) {
                    }

                    try {
                        $table->dropIndex('tasks_status_due_date_index');
                    } catch (\Exception $e) {
                    }
                });
            }

            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('due_date');
            });
        }

        if (Schema::hasColumn('tasks', 'due_at') && !Schema::hasColumn('tasks', 'due_date')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->renameColumn('due_at', 'due_date');
            });

            Schema::table('tasks', function (Blueprint $table) {
                // Recreate the index
                $table->index(['status', 'due_date'], 'tasks_status_due_date_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['completed_by']);
            $table->dropColumn(['completed_by', 'completed_at']);

            // Revert due_date to date (simplified)
            $table->date('old_due_date')->nullable();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('old_due_date', 'due_date');
        });
    }
};
