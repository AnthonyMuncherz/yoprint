<?php

namespace App\Events;

use App\Models\FileUpload;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileProcessingUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public FileUpload $fileUpload
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('file-processing'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->fileUpload->id,
            'filename' => $this->fileUpload->original_name,
            'status' => $this->fileUpload->status,
            'total_records' => $this->fileUpload->total_records,
            'processed_records' => $this->fileUpload->processed_records,
            'failed_records' => $this->fileUpload->failed_records,
            'progress_percentage' => $this->fileUpload->progress_percentage,
            'error_message' => $this->fileUpload->error_message,
            'created_at' => $this->fileUpload->created_at,
            'completed_at' => $this->fileUpload->completed_at,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'file.processing.update';
    }
}
