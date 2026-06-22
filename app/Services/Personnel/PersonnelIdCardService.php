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

        $mime = Storage::disk('local')->mimeType($path) ?: 'image/jpeg';
        $contents = Storage::disk('local')->get($path);

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) {
            $converted = $this->convertImageToPng($contents);

            if ($converted !== null) {
                $mime = 'image/png';
                $contents = $converted;
            }
        }

        $data = base64_encode($contents);

        return "data:{$mime};base64,{$data}";
    }

    private function convertImageToPng(string $contents): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagepng')) {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            return null;
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return $png === false ? null : $png;
    }
}
