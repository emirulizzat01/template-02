<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environments', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
        });

        Schema::create('environment_user', function (Blueprint $table): void {
            $table->foreignId('environment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['environment_id', 'user_id']);
        });

        Schema::create('monitored_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('environment_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status');
            $table->timestamp('checked_at')->nullable();
            $table->text('description')->nullable();
            $table->unique(['environment_id', 'name']);
        });

        Schema::create('request_buckets', function (Blueprint $table): void {
            $table->foreignId('environment_id')->constrained()->cascadeOnDelete();
            $table->timestamp('bucket_at');
            $table->unsignedBigInteger('success');
            $table->unsignedBigInteger('errors');
            $table->primary(['environment_id', 'bucket_at']);
        });

        Schema::create('monitoring_snapshots', function (Blueprint $table): void {
            $table->foreignId('environment_id')->constrained()->cascadeOnDelete();
            $table->primary('environment_id');
            $table->timestamp('observed_at');
            $table->unsignedBigInteger('healthy_checks');
            $table->unsignedBigInteger('total_checks');
            $table->unsignedBigInteger('latency_sum_ms');
            $table->unsignedBigInteger('latency_samples');
            $table->unsignedBigInteger('used_capacity');
            $table->unsignedBigInteger('total_capacity');
            $table->unsignedBigInteger('successful_deployments');
            $table->unsignedBigInteger('finished_deployments');
        });

        Schema::create('deployments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('monitored_service_id')->constrained()->cascadeOnDelete();
            $table->string('version');
            $table->string('commit');
            $table->string('actor');
            $table->string('status');
            $table->timestamp('deployed_at')->index();
        });

        Schema::create('incidents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('monitored_service_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity');
            $table->string('status');
            $table->timestamp('opened_at')->index();
        });
    }

    public function down(): void
    {
        foreach (['incidents', 'deployments', 'monitoring_snapshots', 'request_buckets', 'monitored_services', 'environment_user', 'environments'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
