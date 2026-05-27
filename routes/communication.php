<?php

use App\Http\Controllers\AnnouncementAttachmentController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementReadController;
use App\Http\Controllers\AnnouncementTargetController;
use App\Http\Controllers\ChatAttachmentController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\ChatReadController;
use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\ChatRoomMemberController;
use App\Http\Controllers\CommunicationDashboardController;
use App\Http\Controllers\NotificationCenterController;
use Illuminate\Support\Facades\Route;

Route::get('/communication', [CommunicationDashboardController::class, 'index'])
    ->middleware('permission:view_announcements')
    ->name('communication.dashboard');

Route::middleware('permission:view_announcements')->group(function (): void {
    Route::get('/announcements', [AnnouncementController::class, 'index'])
    ->name('announcements.index');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])
        ->name('announcements.show');
    Route::post('/announcements/{announcement}/read', [AnnouncementReadController::class, 'markRead'])
        ->name('announcements.read.mark');
    Route::post('/announcements/{announcement}/acknowledge', [AnnouncementReadController::class, 'acknowledge'])
        ->name('announcements.acknowledge');
});

Route::middleware('permission:create_announcement')->group(function (): void {
    Route::post('/announcements', [AnnouncementController::class, 'store'])
        ->name('announcements.store');
});

Route::middleware('permission:edit_announcement')->group(function (): void {
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])
        ->name('announcements.update');
    Route::put('/announcements/{announcement}/targets', [AnnouncementTargetController::class, 'update'])
        ->name('announcements.targets.update');
    Route::post('/announcements/{announcement}/attachments', [AnnouncementAttachmentController::class, 'store'])
        ->name('announcements.attachments.store');
});

Route::middleware('permission:publish_announcement')->group(function (): void {
    Route::post('/announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])
        ->name('announcements.publish');
});

Route::middleware('permission:archive_announcement')->group(function (): void {
    Route::post('/announcements/{announcement}/archive', [AnnouncementController::class, 'archive'])
        ->name('announcements.archive');
});

Route::middleware('permission:view_announcement_reads')->group(function (): void {
    Route::get('/announcements/{announcement}/reads', [AnnouncementReadController::class, 'index'])
        ->name('announcements.reads.index');
});

Route::middleware('permission:view_announcements')->group(function (): void {
    Route::get('/announcements/attachments/{attachment}/download', [AnnouncementAttachmentController::class, 'download'])
        ->name('announcements.attachments.download');
});

Route::middleware('permission:access_chat')->group(function (): void {
    Route::get('/chat', [ChatRoomController::class, 'index'])->name('chat.index');
    Route::get('/chat/rooms/{room}', [ChatRoomController::class, 'show'])->name('chat.rooms.show');
    Route::post('/chat/rooms/{room}/read', [ChatReadController::class, 'markRoomRead'])->name('chat.rooms.read.mark');
    Route::get('/chat/rooms/{room}/unread-count', [ChatReadController::class, 'unreadCount'])->name('chat.rooms.unread-count');
    Route::get('/chat/attachments/{attachment}/download', [ChatAttachmentController::class, 'download'])->name('chat.attachments.download');
});

Route::middleware('permission:create_chat_room')->group(function (): void {
    Route::post('/chat/rooms', [ChatRoomController::class, 'store'])->name('chat.rooms.store');
});

Route::middleware('permission:manage_chat_room_members')->group(function (): void {
    Route::put('/chat/rooms/{room}', [ChatRoomController::class, 'update'])->name('chat.rooms.update');
    Route::post('/chat/rooms/{room}/archive', [ChatRoomController::class, 'archive'])->name('chat.rooms.archive');
    Route::get('/chat/rooms/{room}/members', [ChatRoomMemberController::class, 'index'])->name('chat.rooms.members.index');
    Route::post('/chat/rooms/{room}/members', [ChatRoomMemberController::class, 'store'])->name('chat.rooms.members.store');
    Route::delete('/chat/rooms/{room}/members/{userId}', [ChatRoomMemberController::class, 'destroy'])->name('chat.rooms.members.destroy');
});

Route::middleware('permission:send_chat_message')->group(function (): void {
    Route::post('/chat/rooms/{room}/messages', [ChatMessageController::class, 'store'])->name('chat.messages.store');
});

Route::middleware('permission:edit_chat_message')->group(function (): void {
    Route::put('/chat/messages/{message}', [ChatMessageController::class, 'update'])->name('chat.messages.update');
});

Route::middleware('permission:delete_chat_message')->group(function (): void {
    Route::delete('/chat/messages/{message}', [ChatMessageController::class, 'destroy'])->name('chat.messages.destroy');
});

Route::middleware('permission:view_notification_center')->group(function (): void {
    Route::get('/communication/notifications', [NotificationCenterController::class, 'index'])->name('communication.notifications.index');
    Route::post('/communication/notifications/read-all', [NotificationCenterController::class, 'markAllRead'])->name('communication.notifications.read-all');
    Route::post('/communication/notifications/{notification}/read', [NotificationCenterController::class, 'markRead'])->name('communication.notifications.read');
});
