<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Speech-to-text transcription for voice recordings.
 * Replace transcribe() implementation with your preferred provider (Google Cloud Speech-to-Text, Whisper API, etc.).
 */
class TranscribeService
{
    /**
     * Transcribe an audio file to text.
     *
     * @param string $path Full path to the stored audio file (e.g. from Storage::path(...))
     * @return string|null Transcript text or null on failure
     */
    public function transcribe(string $path): ?string
    {
        if (!file_exists($path)) {
            Log::warning('TranscribeService: file not found', ['path' => $path]);
            return null;
        }

        // Placeholder: return null so frontend can still use manual input.
        // To enable STT, integrate a provider below and return the transcript.
        //
        // Example (Google Cloud Speech-to-Text):
        // return $this->transcribeWithGoogle($path);
        //
        // Example (OpenAI Whisper API):
        // return $this->transcribeWithWhisper($path);

        Log::info('TranscribeService: no provider configured, returning placeholder');
        return null;
    }

    /**
     * Check if transcription is available (e.g. API key configured).
     */
    public function isAvailable(): bool
    {
        return false;
    }
}
