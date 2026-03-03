<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Request Form</title>
    <style>
        body { font-family: Cambria, sans-serif; font-size: 8px; }
        .header { text-align: center; font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td, th { border: 1px solid #000; padding: 3px; vertical-align: top; }
        .section-title { font-weight: bold; background: #f2f2f2; }
        @page { size: A4 landscape; margin: 1mm; }
    </style>
</head>

<body>

@php
    use App\Repositories\OptionRepo;

    $requester   = $form->requester;
    $rf          = $form->request_form;

    $normalizeList = function ($value) {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    };

    $arrayToString = function ($value) use ($normalizeList) {
        $list = $normalizeList($value);
        return empty($list) ? '' : implode(', ', $list);
    };

    $dateRequested = $form->created_at->format('F d, Y');
    $dateGenerated = now();
    $requestId     = $form->id;

    $requesttype = $arrayToString($rf->request_type);

    $laboratoriesSource = !empty($normalizeList($rf->laboratories_labels ?? []))
        ? ($rf->laboratories_labels ?? [])
        : ($rf->labs_to_use ?? []);

    $equipmentsSource = !empty($normalizeList($rf->equipments_labels ?? []))
        ? ($rf->equipments_labels ?? [])
        : ($rf->equipments_to_use ?? []);

    $consumablesSource = !empty($normalizeList($rf->consumables_labels ?? []))
        ? ($rf->consumables_labels ?? [])
        : ($rf->consumables_to_use ?? []);

    $labs       = $arrayToString($laboratoriesSource);
    $equipments = $arrayToString($equipmentsSource);
    $supplies   = $arrayToString($consumablesSource);

    $requesterName = strtoupper($requester->name ?? '');
    $approverName  = strtoupper($form->approved_by ?? '');
    $centerChief   = app(OptionRepo::class)->getCenterChief();

    $forPdf = $forPdf ?? true; // Default to PDF context for DomPDF generator

    $getImagePath = function($path) use ($forPdf) {
        if ($forPdf) {
            return public_path($path);
        }
        return '/' . ltrim($path, '/');
    };

    $logos = [
        'cbc'     => $getImagePath('imgs/logo-black.png'),
        'overlay' => $getImagePath('imgs/Overlay.png'),
        'da'      => $getImagePath('imgs/da_bpo.png'),
        'bp'      => $getImagePath('imgs/bagong_pilipinas.png'),
    ];
@endphp


<table style="border:none;border-collapse:collapse;">
    <tr>
        @foreach(['REQUESTOR\'S COPY','OFFICER\'S COPY'] as $copy)

        <td style="width:48%;padding:3mm;box-sizing:border-box;border:none;border-collapse:collapse;">
            <div style="position:relative">
            <table style="border:none; width:100%; font-family:'Times New Roman'; border-collapse:collapse;">
                <tr style="border:none">
                    <!-- Logo -->
                    <td style="border:none; width:30px; vertical-align:middle;">
                        <img src="{{ $logos['cbc'] }}" style="height:40px;">
                    </td>

                    <!-- Text block -->
                    <td style="border:none; vertical-align:middle; padding-left:6px;">
                        <div style="font-size:8px; line-height:10px;">
                            Department of Agriculture
                        </div>

                        <div style="font-size:10px; font-weight:bold; color:#4CAF50; line-height:12px;">
                            CROP BIOTECHNOLOGY CENTER
                        </div>

                        <div style="font-size:6px; line-height:8px;">
                            DA-PhilRice Compound, Muñoz, Nueva Ecija
                        </div>
                    </td>

                </tr>
            </table>

                <img src="{{ $logos['overlay'] }}" style="height:90px;position:absolute;right:0;top:0;z-index:-1">
            </div>

            <div class="header">
                <h3 style="font-size:10px">FACILITIES, EQUIPMENT, and SUPPLIES REQUEST FORM</h3>
                <div style="opacity: 50%;">{{ $copy }}</div>
            </div>

            <table style="border: none; border-collapse: collapse;">
                <tr><td><b>Date of Request</b></td><td>{{ $dateRequested }}</td><td><b>Affiliation</b></td><td>{{ $requester->affiliation }}</td></tr>
                <tr><td><b>Requestor</b></td><td>{{ $requester->name }}</td><td><b>Contact</b></td><td>{{ $requester->phone }}</td></tr>
                <tr><td><b>Position</b></td><td>{{ $requester->position }}</td><td><b>Email</b></td><td>{{ $requester->email }}</td></tr>

                <tr class="section-title"><td colspan="4">Request Details</td></tr>

                <tr><td colspan="1"><b>Type</b></td><td colspan="3">{{ $requesttype }}</td></tr>
                <tr><td colspan="1"><b>Details</b></td><td colspan="3">{{ $rf->request_details }}</td></tr>
                <tr><td colspan="1"><b>Purpose</b></td><td colspan="3">{{ $rf->request_purpose }}</td></tr>
                <tr><td colspan="1"><b>Project</b></td><td colspan="3">{{ $rf->project_title }}</td></tr>
                <tr><td colspan="1"><b>Date/Time</b></td><td colspan="1">{{ $rf->date_of_use }} {{ $rf->time_of_use }}</td><td colspan="1"><b>Expected Return</b></td><td colspan="1">{{ $rf->date_of_use }} {{ $rf->time_of_use }}</td></tr>
                
                @if(!empty($labs))
                <tr><td colspan="1"><b>Laboratory</b></td><td colspan="3">{{ $labs }}</td></tr>
                @endif

                @if(!empty($equipments))
                <tr><td colspan="1"><b>Equipment</b></td><td colspan="3">{{ $equipments }}</td></tr>
                @endif

                @if(!empty($supplies))
                <tr><td colspan="1"><b>Supplies</b></td><td colspan="3">{{ $supplies }}</td></tr>
                @endif

            </table>

            <p><b>Liability Clause:</b></p>
            <ul  style="text-align: justify">
                <li>I hereby acknowledge that I will utilize the supply/equipment/laboratory at my own risk; and agree to use it
                    responsibly and in accordance with any provided instructions or safety guidelines
                </li>
                <li>I agree to assume full responsibility for any damage or loss of the equipment while it is in my possession.</li>
                <li>I agree that the Center shall not be held liable for the quality, accuracy, reliability, or completeness of any
                    data generated by the Requestor using the lab's facilities, equipment, or resources. The Requestor assumes full
                    responsibility for the design, execution, and interpretation of the experiments and the data derived therefrom.
                    The Center makes no warranties, express or implied, regarding the outcomes of the Requestor's research
                    activities.
                </li>
            </ul>

            <br>

        <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="width: 50%; padding-bottom: 40px; text-align: left; border: none;">
                    <div style="width: 200px; display: inline-block; text-align: center;">
                        <div style="font-weight: bold; padding-bottom: 2px; text-transform: uppercase;">
                            {{ $requesterName }}
                        </div>
                        <div style="border-top: 1px solid #000; font-style: italic; padding-top: 3px;">
                            Name and Signature of the Requestor
                        </div>
                    </div>
                </td>
                <td style="width: 50%; padding-bottom: 40px; text-align: right; border: none;">
                    <div style="width: 200px; display: inline-block; text-align: center;">
                        <div style="font-weight: bold; padding-bottom: 2px; text-transform: uppercase;">
                            {{ $approverName }}
                        </div>
                        <div style="border-top: 1px solid #000; font-style: italic; padding-top: 3px;">
                            Approving Officer
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="2" style="text-align: left; border: none;">
                    <div style="width: 200px; text-align: left; margin-bottom: 5px;">
                        Noted By:
                    </div>
                    <div style="width: 200px; display: inline-block; text-align: center;">
                        <div style="font-weight: bold; padding-bottom: 2px; text-transform: uppercase;">
                            {{ $centerChief }}
                        </div>
                        <div style="border-top: 1px solid #000; font-style: italic; padding-top: 3px;">
                            Center Chief
                        </div>
                    </div>
                </td>
            </tr>
        </table>

            <br>
            @if($form->approval_constraint)<p><b>Approval Remarks:</b> {{ $form->approval_constraint }}</p> @endif
            @if($form->disapproved_remarks)<p><b>Disapproval Remarks:</b> {{ $form->disapproved_remarks }}</p> @endif

            <footer style="margin-top: 5mm; position: absolute; bottom: 0; width: 48%;">
                <table style="width: 100%; border: none; border-collapse: collapse;">
                    <tr style="height: 30px;">
                        <!-- Left side -->
                        <td style="text-align: left; border: none; vertical-align: top; padding: 0;">
                            <h3 style="font-size: 8px !important; margin: 0; font-weight: bold; color: #4CAF50; font-family: 'Times New Roman'">
                                Biotech for Better Crop for Better Lives
                            </h3>
                            <div style="font-size: 6px !important;">Email: cropbiotechcenter@gmail.com</div>
                            <div style="font-size: 6px !important;">Website: dacbc.philrice.gov.ph</div>
                            <div style="font-size: 6px !important;">Social Media: www.facebook.com/DACropBiotechCenter</div>
                        </td>

                        <td style="border: none; font-size: 6px !important; text-align: left; vertical-align: top;">
                            <div>SYSTEM GENERATED</div>
                            <div>Date Generated: {{ now() }}</div>
                            <div>Request ID: {{ $form->id  }}</div>
                        </td>

                        <!-- Right side -->
                        <td style="text-align: right; vertical-align: bottom; border: none; padding-right: 20px;">
                            <img src="{{  $logos['da'] }}" style="width: 30px; height: auto;">
                            <img src="{{  $logos['bp'] }}" style="width: 30px; height: auto;">
                        </td>
                    </tr>
                </table>
            </footer>
            
            @if ($copy === 'REQUESTOR\'S COPY')
                <!-- Cutting guide line -->
                <td style="width:2mm;border:none;border-right:2px dashed #d0d0d0; padding: 0; margin: 0;"></td>
            @endif
        </td>
    @endforeach
    </tr>
</table>
</body>
</html>
