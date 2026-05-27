<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatMessageAttachment;

class ChatAttachmentService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function create(ChatMessage $message, array $payload): ChatMessageAttachment
    {
        $attachment = ChatMessageAttachment::query()->create([
            'chat_message_id' => $message->id,
            'file_name' => $payload['file_name'],
            'file_path' => $payload['file_path'],
            'file_type' => $payload['file_type'],
            'file_size' => $payload['file_size'],
            'uploaded_by' => auth()->id(),
        ]);

        $this->auditLogService->record('communication', 'chat_file_shared', [], $attachment->toArray(), $message->branch_id, 'Chat file shared');

        return $attachment;
    }
}
