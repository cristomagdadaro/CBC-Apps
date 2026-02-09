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
    case GENETICTRANSROOM = '05'; // Genome Enineering Laboratory
    case TISSUECULTUREROOM = '06'; // Tissue Culture Room
    case SYSTEMBIOLOGYROOM = '07'; // System Biology Room
    case MICROBIALBIOTECHROOM = '08'; // Microbial Biotechnology Room
    case MOLECULARDIAGNOSTICSROOM = '09'; // Molecular Diagnostics Room

    case CENTRALBODEGA = '10'; // Central Bodega

    case BODEGAONE = '11'; // Bodega 1

    case BODEGATWO = '12'; // Bodega 2

    case PLENARY = '13'; // Plenary Hall        
    case TRAININGROOM = '14'; // Training Room

    case MPH = '15'; // Multi-Purpose Hall

    case INNOVA = 'INNOVA'; // change into plate number

    case PICKUP = 'PICKUP'; //  change into plate number

    case VAN = 'VAN'; // change into plate number

    case SUV = 'SUV'; // change into plate number

    case COASTER = 'COASTER'; // change into plate number

    case EBIKE = 'EBIKE'; // change into plate number

    case BIKE = 'BIKE'; // change into plate number

    case TRACTOR = 'TRACTOR'; // change into plate number
}
