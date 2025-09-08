<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Request Form</title>
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 12px;
            }

            .header {
                text-align: center;
                font-weight: bold;
                margin-bottom: 10px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 10px;
            }

            td, th {
                border: 1px solid #000;
                padding: 6px;
                vertical-align: top;
            }

            .section-title {
                font-weight: bold;
                background: #f2f2f2;
            }

            .checkbox {
                display: inline-block;
                width: 12px;
                height: 12px;
                border: 1px solid #000;
                margin-right: 5px;
            }

            @page {
                margin: 0mm;
            }

            body {
                font-family: Cambria, sans-serif;
            }
        </style>
    </head>
    <body>
        <div style="position: relative">
            <table style="width: 100%; border-collapse: collapse; border: none !important; padding: 15px 20px; font-family: 'Times New Roman'">
                <tr style="border: none !important;">
                    <!-- Left Logo -->
                    <td style="width: 30px; vertical-align: middle; text-align: left; border: none !important;">
                        <img src="{{ public_path('imgs/logo-black.png') }}" style="height: 60px;">
                    </td>

                    <!-- Center Text -->
                    <td style="width: 100%; vertical-align: middle; text-align: left; line-height: 1; border: none !important;">
                        <div style="font-size: 12px; margin: 0; font-weight: normal;">
                            Department of Agriculture
                        </div>
                        <div style="font-size: 16px; margin: 0; font-weight: bold; color: #4CAF50;">
                            CROP BIOTECHNOLOGY CENTER
                        </div>
                        <div style="font-size: 10px; margin: 0; font-weight: normal;">
                            DA-PhilRice Compound, Maligaya, Science City of Muñoz, 3119 Nueva Ecija
                        </div>
                    </td>
                </tr>
            </table>
            <!-- Right Overlay -->
            <img src="{{ public_path('imgs/Overlay.png') }}" style="height: 160px; position: absolute; right: 0; top: 0;">
        </div>

        <div style="position: absolute; top: 100px; left: 50%; transform: translateX(-50%); width: 80%; font-family: 'Times New Roman'">
            <div class="header">
                <h3>LABORATORY USE, EQUIPMENT, AND SUPPLY REQUEST FORM</h3>
            </div>
            <table>
                <tr>
                    <td style="font-weight: bold">Date of Request:</td>
                    <td>{{ $form->created_at->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Requestor's Name:</td>
                    <td>{{ $form->requester->name }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Position:</td>
                    <td>{{ $form->requester->position }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Affiliation/Department:</td>
                    <td>{{ $form->requester->affiliation }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Contact Number:</td>
                    <td>{{ $form->requester->phone }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Email Address:</td>
                    <td>{{ $form->requester->email }}</td>
                </tr>
                <tr class="section-title">
                    <td colspan="2">Details of Request</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Request Type:</td>
                    <td>{{ $form->request_form->request_type }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Details of Request:</td>
                    <td>{{ $form->request_form->request_details }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Purpose of Request:</td>
                    <td>{{ $form->request_form->request_purpose }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Project Title:</td>
                    <td>{{ $form->request_form->project_title }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Date and Time of Use:</td>
                    <td>{{ $form->request_form->date_of_use }} {{ $form->request_form->time_of_use }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Laboratory to be Used:</td>
                    <td>{{ implode(', ', json_decode($form->request_form->labs_to_use ?? '[]', true)) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Equipment Needed:</td>
                    <td>{{ implode(', ', json_decode($form->request_form->equipments_to_use ?? '[]', true)) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold">Supplies Needed:</td>
                    <td>{{ implode(', ', json_decode($form->request_form->consumables_to_use ?? '[]', true)) }}</td>
                </tr>
            </table>

            <p><b>Liability Clause:</b></p>
            <ul  style="text-align: justify">
                <li>I hereby acknowledge that I will utilize the supply/equipment/laboratory at my own risk; and agree to use it
                    responsibly and in accordance with any provided instructions or safety guidelines
                </li>
                <li>I agree to assume full responsibility for any damage or loss of the equipment while it is in my possession.</li>
                <li>I agree that the Center shall not be held liable for the quality, accuracy, reliability, or completeness of any
                    data generated by the Requestor using the lab’s facilities, equipment, or resources. The Requestor assumes full
                    responsibility for the design, execution, and interpretation of the experiments and the data derived therefrom.
                    The Center makes no warranties, express or implied, regarding the outcomes of the Requestor’s research
                    activities.
                </li>
            </ul>

            <br>

            <div style="width: 250px; text-align: center">
                {{ strtoupper($form->requester->name ?? '') }}
                <p style="font-style: italic; border-top: 1px solid #000000; padding-top: 5px; width: 250px;">
                    Name and Signature of the Requestor
                </p>
            </div>

            <br>
            <br>

            <div style="width: 250px; text-align: center">
                {{ strtoupper($form->approved_by ?? '') }}
                <p style="font-style: italic; border-top: 1px solid #000000; padding-top: 5px; width: 250px;">
                    Laboratory Manager/Approving Officer
                </p>
            </div>
            <br>
            <p @if($form->approval_constraint) @endif><b>Approval Remarks:</b> {{ $form->approval_constraint }}</p>
            <p @if($form->disapproved_remarks) @endif><b>Disapproval Remarks:</b> {{ $form->disapproved_remarks }}</p>

        </div>
        <footer style="position: fixed; bottom: 0; left: 0; right: 0; width: 100%;">
            <div style="text-align: center; font-size: 10px !important; opacity: 50%">
                SYSTEM GENERATED
            </div>
            <table style="width: 100%; border: none; border-collapse: collapse;">
                <tr style="height: 60px;">
                    <!-- Left side -->
                    <td style="text-align: left; border: none; padding-left: 20px; vertical-align: top;">
                        <h3 style="font-size: 12px !important; margin: 0; font-weight: bold; color: #4CAF50; font-family: 'Times New Roman'">
                            Biotech for Better Crop for Better Lives
                        </h3>
                        <div style="font-size: 10px !important;">Email: cropbiotechcenter@gmail.com</div>
                        <div style="font-size: 10px !important;">Social Media: www.facebook.com/DACropBiotechCenter</div>
                    </td>

                    <td style="border: none; font-size: 10px !important; text-align: left; vertical-align: top;">
                        <div>Date Generated: {{ now() }}</div>
                        <div>Request ID: {{ $form->id  }}</div>
                    </td>

                    <!-- Right side -->
                    <td style="text-align: right; vertical-align: bottom; border: none; padding-right: 20px;">
                        <img src="{{ '/public/imgs/da_bpo.png' }}" style="width: 50px; height: auto;">
                        <img src="{{ '/public/imgs/bagong_pilipinas.png' }}" style="width: 50px; height: auto;">
                    </td>
                </tr>
            </table>
        </footer>
    </body>
</html>
