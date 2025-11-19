<?php

namespace App\Enums;

enum Inventory: string
{
    case INCOMING = 'incoming';
    case OUTGOING = 'outgoing';

    case ROI = '01'; // Researchers' Office I
    case ROII = '02'; // Researchers' Office II
    case BIOINFOROOM = '03'; // Bioinformatics Room
    case MOLECULARGENETICSROOM = '04'; // Molecular Genetics Room
    case GENETICTRANSROOM = '05'; // Genetic Transformation Room
    case TISSUECULTUREROOM = '06'; // Tissue Culture Room
    case SYSTEMBIOLOGYROOM = '07'; // System Biology Room
    case MICROBIALBIOTECHROOM = '08'; // Microbial Biotechnology Room
    case MOLECULARDIAGNOSTICSROOM = '09'; // Molecular Diagnostics Room
}
