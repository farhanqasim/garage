-- Add voice_path and voice_transcript to items table (for temporary product voice recording)
-- Run this if you get: Unknown column 'voice_path' in 'field list'

ALTER TABLE `items`
ADD COLUMN `voice_path` VARCHAR(255) NULL AFTER `notes`,
ADD COLUMN `voice_transcript` TEXT NULL AFTER `voice_path`;
