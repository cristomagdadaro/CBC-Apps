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

    case DIAGNOSTICLAB = '16'; // Diagnostic Laboratory

    case PHENOTYPINGAREA = '17'; // Phenotyping Area

    case MICROSCOPESEQUENCEROOM = '18'; // Microscope and Sequencing Room

    case GENERALEQUIPMENTAREA = '19'; // General Equipment Area

    case SAMPLEPROCESSINGROOM = '20'; // Sample Processing Room

    case WASHROOMI = '21'; // Wash Room I

    case MOTORPOOL = '22'; // Motor Pool

    case AADELACRUZ = '23'; // AADelaCruz Office

    case RRSURALTA = '24'; // RR Suralta Office

    case AAOFFICE = '25'; // AA Office

    case MEETINGROOM = '26'; // Meeting Room

    case SUPPLIESROOM1 = '27'; // Supplies Room I

    case SUPPLIESROOM2 = '28'; // Supplies Room II

    case CONSULTANTOFFICE = '29'; // Consultant Office

    case DNAEXTRACTIONROOM = '30'; // DNA Extraction Room

    case DARKROOM = '31'; // Dark Room

    case FREEZERROOM = '32'; // Freezer Room

    case LIGHTROOM = '33'; // Light Room

    case SCREENHOUSE1 = '34'; // Screenhouse 1

    case WASHROOMII = '35'; // Wash Room II

    case INNOVA = 'INNOVA'; // change into plate number

    case PICKUP = 'PICKUP'; //  change into plate number

    case VAN = 'VAN'; // change into plate number

    case SUV = 'SUV'; // change into plate number

    case COASTER = 'COASTER'; // change into plate number

    case EBIKE = 'EBIKE'; // change into plate number

    case BIKE = 'BIKE'; // change into plate number

    case TRACTOR = 'TRACTOR'; // change into plate number
}
