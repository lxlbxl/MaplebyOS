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
        // Add indexes to clients table
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (!Schema::hasIndex('clients', 'clients_email_index')) {
                    $table->index('email');
                    $table->index('is_active');
                    $table->index('type');
                }
            });
        }

        // Add indexes to leads table
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (!Schema::hasIndex('leads', 'leads_status_index')) {
                    $table->index('status');
                    $table->index('client_id');
                    $table->index('assigned_to');
                    $table->index('service_type');
                    $table->index('priority');
                    $table->index(['status', 'assigned_to']);
                }
            });
        }

        // Add indexes to jobs table
        if (Schema::hasTable('jobs')) {
            Schema::table('jobs', function (Blueprint $table) {
                if (!Schema::hasIndex('jobs', 'jobs_status_index')) {
                    $table->index('status');
                    $table->index('client_id');
                    $table->index('assigned_to');
                    $table->index('quote_id');
                    $table->index('job_number');
                    $table->index(['status', 'assigned_to']);
                }
            });
        }

        // Add indexes to quotes table
        if (Schema::hasTable('quotes')) {
            Schema::table('quotes', function (Blueprint $table) {
                if (!Schema::hasIndex('quotes', 'quotes_status_index')) {
                    $table->index('status');
                    $table->index('client_id');
                    $table->index('quote_number');
                    $table->index('lead_id');
                }
            });
        }

        // Add indexes to vehicles table
        if (Schema::hasTable('vehicles')) {
            Schema::table('vehicles', function (Blueprint $table) {
                if (!Schema::hasIndex('vehicles', 'vehicles_status_index')) {
                    $table->index('status');
                    $table->index('buyer_id');
                    $table->index('supplier_id');
                }
            });
        }

        // Add indexes to tasks table
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (!Schema::hasIndex('tasks', 'tasks_status_index')) {
                    $table->index('status');
                    $table->index('assigned_to');
                    $table->index('due_date');
                    $table->index(['status', 'assigned_to']);
                    $table->index(['status', 'due_date']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['type']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['client_id']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['service_type']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['status', 'assigned_to']);
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['client_id']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['quote_id']);
            $table->dropIndex(['job_number']);
            $table->dropIndex(['status', 'assigned_to']);
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['client_id']);
            $table->dropIndex(['quote_number']);
            $table->dropIndex(['lead_id']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['buyer_id']);
            $table->dropIndex(['supplier_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['status', 'assigned_to']);
            $table->dropIndex(['status', 'due_date']);
        });
    }
};
