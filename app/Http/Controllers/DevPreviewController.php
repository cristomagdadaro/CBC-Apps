<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class DevPreviewController extends BaseController
{
    public function __construct()
    {
        $this->service = null;
    }

    protected array $directories = [
        'emails',
        'errors',
        'generator/pdf',
        'golinks'
    ];

    public function index()
    {
        $views = [];
        foreach ($this->directories as $dir) {
            $path = resource_path('views/' . $dir);
            if (File::exists($path)) {
                $files = File::allFiles($path);
                foreach ($files as $file) {
                    if ($file->getExtension() === 'php' && str_ends_with($file->getFilename(), '.blade.php')) {
                        $viewPath = $dir . '/' . str_replace('.blade.php', '', $file->getRelativePathname());
                        // Replace slashes with dots for Laravel view name
                        $viewName = str_replace('/', '.', $viewPath);
                        $viewName = str_replace('\\', '.', $viewName);
                        $views[$dir][] = $viewName;
                    }
                }
            }
        }

        return view('dev.preview-index', compact('views'));
    }

    public function show(Request $request)
    {
        $viewName = $request->query('view');
        
        if (!$viewName || !View::exists($viewName)) {
            abort(404, "View not found: " . $viewName);
        }

        $mockData = $this->getMockData($viewName);
        
        $viewPath = View::make($viewName)->getPath();
        $content = File::get($viewPath);

        // If it's a Markdown mailable, render it properly using the Markdown engine
        if (str_contains($content, 'mail::message') || str_contains($content, 'mail::layout')) {
            return new \Illuminate\Support\HtmlString(
                app(\Illuminate\Mail\Markdown::class)->render($viewName, $mockData)
            );
        }
        
        // Fallback for regular HTML mail components
        if (!View::exists('mail::message')) {
            $mailPath = base_path('vendor/laravel/framework/src/Illuminate/Mail/resources/views');
            if (is_dir($mailPath)) {
                View::addNamespace('mail', $mailPath);
            }
        }
        
        return view($viewName, $mockData);
    }

    /**
     * Define mock data for specific views here to avoid undefined variable errors.
     */
    protected function getMockData(string $viewName): array
    {
        $mocks = [
            'emails.team-invitation' => [
                'invitation' => (object) [
                    'team' => (object) ['name' => 'Development Team']
                ],
                'acceptUrl' => 'http://localhost/accept-invitation'
            ],
            'emails.event-subform-response-notification' => [
                'response' => (object) [
                    'id' => 12345,
                    'formParent' => (object) [
                        'event_id' => 'EVT-999',
                        'form' => (object) ['title' => 'Annual Conference']
                    ],
                    'subform_type' => 'Registration',
                    'created_at' => now(),
                    'response_data' => [
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'company' => 'CBC'
                    ]
                ]
            ],
            'emails.generated-certificate' => [
                'eventId' => 'EVT-2026-001'
            ],
            'emails.notifications.hourly-summary' => [
                'groupedLogs' => [
                    'Equipment Issues' => [
                        (object) ['payload_meta' => ['issue' => 'Broken centrifuge', 'message' => 'Lid will not close properly']]
                    ]
                ]
            ],
            'emails.laboratory.equipment-log-overdue' => [
                'log' => (object) [
                    'personnel' => (object) ['fname' => 'Jane', 'mname' => 'A.', 'lname' => 'Doe', 'suffix' => ''],
                    'equipment' => (object) ['name' => 'Microscope A1'],
                    'started_at' => now()->subDays(2),
                    'end_use_at' => now()->subDays(1)
                ],
                'equipmentType' => 'ict',
                'equipmentUrl' => 'http://localhost/equipment-session/123'
            ],
            'emails.outgoing-transaction-notification' => [
                'transaction' => (object) [
                    'item' => (object) ['name' => 'Lab Gloves'],
                    'quantity' => 15,
                    'unit' => 'boxes',
                    'remarks' => 'For the new batch of experiments.',
                    'personnel' => (object) ['name' => 'Dr. Smith'],
                    'user' => (object) ['name' => 'Admin User']
                ],
                'remainingQuantity' => 85
            ],
            'emails.personnel.registration-verification' => [
                'registration' => (object) ['full_name' => 'Jane Doe'],
                'url' => 'http://localhost/verify',
                'verificationUrl' => 'http://localhost/verify-email/xyz123'
            ],
            'emails.personnel.registration-approved' => [
                'registration' => (object) ['full_name' => 'Jane Doe'],
                'url' => 'http://localhost/login',
                'card' => [
                    'employee_id' => 'CBC-26-0001',
                    'full_name' => 'Jane Doe',
                    'course_program' => 'BS Computer Science',
                    'date_issued' => now()->format('Y-m-d')
                ]
            ],
            'emails.lab-request.lifecycle' => [
                'request' => (object) [
                    'id' => 'REQ-002',
                    'request_form' => (object) [
                        'id' => 'REQ-002', 
                        'request_purpose' => 'For the new experiment batch',
                        'date_of_use' => now()->addDays(2)->format('Y-m-d'),
                        'date_of_use_end' => now()->addDays(3)->format('Y-m-d'),
                        'time_of_use' => '08:00',
                        'time_of_use_end' => '17:00'
                    ],
                    'requester' => (object) ['name' => 'John Smith'],
                    'approval_constraint' => 'Must clean up after use',
                    'disapproved_remarks' => ''
                ],
                'event' => 'approved',
                'eventLabel' => 'Approved',
                'requestUrl' => 'http://localhost/requests/REQ-002'
            ],
            'golinks.redirect' => [
                'goLink' => (object) [
                    'og_title' => 'Important Document',
                    'og_description' => 'A verified resource link.',
                    'og_image' => null,
                    'public_url' => 'http://go.local/doc',
                    'target_url' => 'https://example.com/document',
                    'clicks' => 42
                ]
            ],
            'golinks.expired' => [
                'goLink' => (object) ['og_title' => 'Expired Link']
            ],
            'generator.pdf.barcode-labels' => [
                'paperWidth' => 5,
                'paperHeight' => 3,
                'printMode' => 'barcode',
                'labels' => [
                    [
                        'name' => 'Microscope Lens',
                        'brand' => 'Zeiss',
                        'barcode' => 'LENS-99901',
                        'barcodeDataUri' => '',
                        'qrSvg' => '<svg width="56" height="56"><rect width="56" height="56" fill="#ccc"/></svg>'
                    ]
                ]
            ],
            'generator.pdf.personnel-id-card' => [
                'card' => [
                    'full_name' => 'Jane Doe',
                    'employee_id' => 'CBC-26-0001',
                    'registration_type_label' => 'Student Trainee',
                    'date_issued' => now()->format('F j, Y'),
                    'photo_data_uri' => '' // Empty string will show placeholder
                ]
            ],
            'generator.pdf.printable-request-form' => [
                'form' => (object) [
                    'id' => 'REQ-001',
                    'created_at' => now(),
                    'approved_by' => 'Admin Boss',
                    'approval_constraint' => 'Clean the area.',
                    'disapproved_remarks' => '',
                    'requester' => (object) [
                        'name' => 'John Doe',
                        'affiliation' => 'CBC Institute',
                        'phone' => '+639123456789',
                        'position' => 'Researcher',
                        'email' => 'johndoe@example.com'
                    ],
                    'request_form' => (object) [
                        'request_purpose' => 'Analyze samples',
                        'request_details' => 'Detailed info',
                        'project_title' => 'Project X',
                        'date_of_use' => '2026-08-10',
                        'time_of_use' => '08:00',
                        'date_of_use_end' => '2026-08-11',
                        'time_of_use_end' => '17:00',
                        'request_type' => ['Analysis', 'Consultation'],
                        'laboratories_labels' => ['Lab 1'],
                        'labs_to_use' => [],
                        'equipments_labels' => ['Microscope'],
                        'equipments_to_use' => [],
                        'consumables_labels' => ['Gloves'],
                        'consumables_to_use' => []
                    ]
                ],
                'rf' => (object) [
                    'request_type' => ['Analysis', 'Consultation'],
                    'request_purpose' => 'Analyze samples',
                    'request_details' => 'Detailed info',
                    'project_title' => 'Project X',
                    'date_of_use' => '2026-08-10',
                    'time_of_use' => '08:00',
                    'date_of_use_end' => '2026-08-11',
                    'time_of_use_end' => '17:00',
                    'laboratories_labels' => ['Lab 1'],
                    'labs_to_use' => [],
                    'equipments_labels' => ['Microscope'],
                    'equipments_to_use' => [],
                    'consumables_labels' => ['Gloves'],
                    'consumables_to_use' => []
                ],
                'arrayToString' => function($array) { return is_array($array) ? implode(', ', $array) : ''; },
                'logos' => [
                    'cbc' => asset('img/logo.png'),
                    'overlay' => asset('img/overlay.png'),
                    'da' => asset('img/da.png'),
                    'bp' => asset('img/bp.png')
                ]
            ],
            'errors.403' => [
                'exception' => new \Exception("This is a mock 403 forbidden exception message for dev preview.")
            ],
            'errors.503' => [
                'exception' => new \Exception("Service unavailable mock message.")
            ]
        ];

        return $mocks[$viewName] ?? [];
    }
}
