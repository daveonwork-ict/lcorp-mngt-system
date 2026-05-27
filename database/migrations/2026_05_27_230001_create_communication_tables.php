<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->string('announcement_number')->unique();
            $table->string('title');
            $table->longText('content');
            $table->string('announcement_type');
            $table->string('priority_level')->default('normal');
            $table->string('target_scope')->default('all_users');
            $table->timestamp('publish_start_at')->nullable();
            $table->timestamp('publish_end_at')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->boolean('requires_acknowledgment')->default(false);
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'publish_start_at', 'publish_end_at']);
            $table->index(['is_urgent', 'is_pinned']);
        });

        Schema::create('announcement_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->unique(['announcement_id', 'target_type', 'target_id'], 'announcement_target_unique');
        });

        Schema::create('announcement_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('announcement_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('read_at')->nullable();
            $table->string('acknowledgment_status')->default('unread');
            $table->timestamps();

            $table->unique(['announcement_id', 'user_id']);
            $table->index(['branch_id', 'acknowledgment_status']);
        });

        Schema::create('chat_rooms', function (Blueprint $table): void {
            $table->id();
            $table->string('room_number')->unique();
            $table->string('room_name');
            $table->string('room_type');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $table->string('status')->default('active');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'room_type', 'branch_id']);
        });

        Schema::create('chat_room_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chat_room_id')->constrained('chat_rooms')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('role_in_room')->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['chat_room_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chat_room_id')->constrained('chat_rooms')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate();
            $table->text('message_body')->nullable();
            $table->string('message_type')->default('text');
            $table->foreignId('parent_message_id')->nullable()->constrained('chat_messages')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('status')->default('sent');
            $table->timestamps();

            $table->index(['chat_room_id', 'created_at']);
            $table->index(['sender_id', 'status']);
        });

        Schema::create('chat_message_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chat_message_id')->constrained('chat_messages')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('chat_message_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chat_message_id')->constrained('chat_messages')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['chat_message_id', 'user_id']);
            $table->index(['user_id', 'read_at']);
        });

        Schema::create('communication_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('announcement_id')->nullable()->constrained('announcements')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('chat_room_id')->nullable()->constrained('chat_rooms')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('chat_message_id')->nullable()->constrained('chat_messages')->nullOnDelete()->cascadeOnUpdate();
            $table->string('category');
            $table->string('title');
            $table->text('message');
            $table->json('payload')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['branch_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_notifications');
        Schema::dropIfExists('chat_message_reads');
        Schema::dropIfExists('chat_message_attachments');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_room_members');
        Schema::dropIfExists('chat_rooms');
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcement_attachments');
        Schema::dropIfExists('announcement_targets');
        Schema::dropIfExists('announcements');
    }
};
