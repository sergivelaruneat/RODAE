<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class MediaUploadRule implements ValidationRule
{
    public function __construct(
        private int $imageMaxMb = 4,
        private int $videoMaxMb = 60,
        private int $videoMaxSeconds = 30
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!($value instanceof UploadedFile) || !$value->isValid()) {
            $fail('El archivo no es válido.'); return;
        }

        $mime   = $value->getMimeType() ?? '';
        $sizeMb = $value->getSize() / (1024 * 1024);

        $imageMimes = ['image/jpeg','image/jpg','image/png','image/webp'];
        $videoMimes = ['video/mp4','video/webm','video/quicktime']; // mov = quicktime

        // Imágenes
        if (in_array($mime, $imageMimes, true)) {
            if ($sizeMb > $this->imageMaxMb) {
                $fail("La imagen no puede superar los {$this->imageMaxMb} MB.");
            }
            return;
        }

        // Vídeos
        if (in_array($mime, $videoMimes, true)) {
            if ($sizeMb > $this->videoMaxMb) {
                $fail("El vídeo no puede superar los {$this->videoMaxMb} MB.");
                return;
            }
            // Duración con getID3 (no requiere ffmpeg)
            try {
                $getID3 = new \getID3();
                $info = $getID3->analyze($value->getRealPath());
                $seconds = isset($info['playtime_seconds']) ? (float)$info['playtime_seconds'] : null;

                if (!is_finite($seconds)) {
                    $fail('No se pudo comprobar la duración del vídeo.'); return;
                }
                if ($seconds > $this->videoMaxSeconds+0.5) { // margen 0.05s
                    $fail("El vídeo debe durar como máximo {$this->videoMaxSeconds} segundos.");
                }
            } catch (\Throwable $e) {
                $fail('No se pudo analizar el vídeo.');
            }
            return;
        }

        $fail('Formato no permitido. Usa JPG, PNG, WEBP para imágenes o MP4/WEBM/MOV para vídeos.');
    }
}
