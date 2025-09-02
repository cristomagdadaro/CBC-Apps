<?php

namespace App\Http\Controllers;
use App\Models\RequestFormPivot;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFGeneratorController extends Controller
{

    public function downloadPdf($id)
    {
        $form = RequestFormPivot::with(['requester','request_form'])->findOrFail($id);

        $pdf = Pdf::loadView('generator/pdf/printable-request-form', compact('form'));
        // to download
        //return $pdf->download('RequestForm-'.$form->id.'.pdf');
        // just view rendered pdf
        return $pdf->stream('RequestForm-'.$form->id.'.pdf');
        // blade format
        //return view('generator/pdf/printable-request-form', compact('form'));
    }
}
