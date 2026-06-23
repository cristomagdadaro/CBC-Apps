<?php

namespace App\Services\Personnel;

use App\Models\PersonnelRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PersonnelIdCardService
{
    public function cardData(PersonnelRegistration $registration, ?string $photoUrl = null, bool $includePhotoDataUri = false): array
    {
        $registration->loadMissing('personnel');

        return [
            'id' => $registration->id,
            'full_name' => $registration->full_name,
            'employee_id' => $registration->personnel?->employee_id ?? $registration->employee_id,
            'course_program' => $registration->course_program,
            'date_issued' => optional($registration->id_issued_at ?? $registration->reviewed_at)->format('M j, Y'),
            'registration_type' => $registration->registration_type,
            'registration_type_label' => $this->typeLabel($registration->registration_type),
            'photo_url' => $photoUrl,
            'photo_data_uri' => $includePhotoDataUri ? $this->photoDataUri($registration) : null,
        ];
    }

    public function pdfBinary(PersonnelRegistration $registration): string
    {
        $card = $this->cardData($registration, includePhotoDataUri: true);

        return Pdf::loadView('generator.pdf.personnel-id-card', compact('card'))
            ->setPaper([0, 0, 209.76, 297.64], 'portrait')
            ->setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
            ->output();
    }

    public function filename(PersonnelRegistration $registration): string
    {
        $name = Str::slug($registration->full_name ?: 'personnel-id');
        $employeeId = Str::slug((string) ($registration->personnel?->employee_id ?? $registration->employee_id ?? 'cbc-id'));

        return "{$name}-{$employeeId}.pdf";
    }

    private function typeLabel(?string $type): string
    {
        return match ($type) {
            PersonnelRegistration::TYPE_STUDENT => 'Student',
            PersonnelRegistration::TYPE_OJT => 'OJT',
            PersonnelRegistration::TYPE_THESIS => 'Thesis',
            default => 'Personnel',
        };
    }

    private function photoDataUri(PersonnelRegistration $registration): ?string
    {
        $path = $registration->id_photo_path;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        $contents = Storage::disk('local')->get($path);
        
        $resized = $this->resizeAndFormatImage($contents);

        if ($resized === null) {
            // Fallback to raw contents if GD fails
            $mime = Storage::disk('local')->mimeType($path) ?: 'image/jpeg';
            $data = base64_encode($contents);
            return "data:{$mime};base64,{$data}";
        }

        $data = base64_encode($resized);
        return "data:image/jpeg;base64,{$data}";
    }

    private function resizeAndFormatImage(string $contents): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Crop to square
        $size = min($width, $height);
        $cropX = (int) (($width - $size) / 2);
        $cropY = (int) (($height - $size) / 2);

        $maxSize = 300;
        $newSize = min($size, $maxSize);

        $bg = imagecreatetruecolor($newSize, $newSize);
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefill($bg, 0, 0, $white);

        imagecopyresampled($bg, $image, 0, 0, $cropX, $cropY, $newSize, $newSize, $size, $size);

        ob_start();
        imagejpeg($bg, null, 85);
        $jpg = ob_get_clean();

        imagedestroy($bg);
        imagedestroy($image);

        return $jpg === false ? null : $jpg;
    }
}
