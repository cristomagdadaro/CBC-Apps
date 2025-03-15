<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Form;

class UpdateExpiredForms extends Command
{
    protected $signature = 'forms:update-expired';
    protected $description = 'Update expired status for forms based on date and time constraints';

    public function handle()
    {
        $forms = Form::where('is_expired', 0)->orWhere('is_expired', false)->get();

        foreach ($forms as $form) {
            if ($form->isExpired()) {
                $form->update(['is_expired' => true]);
                $this->info("Form ID {$form->id} is now expired.");
            }
        }

        $this->info('Expired forms updated successfully.');
    }
}
