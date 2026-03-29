<?php

namespace App\Observers;

use App\Models\RequestFormPivot;
use Illuminate\Support\Facades\File;

class RequestFormPivotObserver
{
    /**
     * Handle the RequestFormPivot "updated" event.
     */
    public function updated(RequestFormPivot $model): void
    {
        $this->purgeCached($model->id);
    }

    /**
     * Handle the RequestFormPivot "deleted" event.
     */
    public function deleted(RequestFormPivot $model): void
    {
        $this->purgeCached($model->id);
    }

    /**
     * Handle the RequestFormPivot "force deleted" event.
     */
    public function forceDeleted(RequestFormPivot $model): void
    {
        $this->purgeCached($model->id);
    }

    /**
     * Remove cached PDFs for this model across all template directories.
     */
    protected function purgeCached(string $id): void
    {
        $base = storage_path('app/private/generated-pdfs');
        if (!File::isDirectory($base)) {
            return;
        }
        $dirs = File::directories($base);
        foreach ($dirs as $dir) {
            $file = $dir . DIRECTORY_SEPARATOR . $id . '.pdf';
            if (File::exists($file)) {
                File::delete($file);
            }
        }
    }
}

