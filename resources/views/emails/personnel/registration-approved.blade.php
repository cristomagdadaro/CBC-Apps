@component('mail::message')
# Welcome to OneCBC!

Hello {{ $registration->full_name ?: 'there' }},

Great news! Your personnel registration has been successfully approved. Your official CBC ID number is **{{ $card['employee_id'] }}**.

We have attached a digital copy of your ID card for printing. Please review your details below to ensure everything is correct.

- **Name:** {{ $card['full_name'] }}
- **CBC ID:** {{ $card['employee_id'] }}
- **Course/Program/Strand/Major:** {{ $card['course_program'] }}
- **Date Issued:** {{ $card['date_issued'] }}

Thank you for registering with us.

Best regards,<br>
DA-Crop Biotechnology Center
@endcomponent
