<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*Category::factory()
            ->count(5)
            ->create();*/

        $categories = [
            ['name' => 'Acids', 'description' => 'Various types of acids including acetic acid, hydrochloric acid, etc.'],
            ['name' => 'Alcohols', 'description' => 'Different alcohols such as ethanol, methanol, isopropanol, etc.'],
            ['name' => 'Amino Acids', 'description' => 'Includes glycine, L-arginine, L-cysteine, etc.'],
            ['name' => 'Antibiotics', 'description' => 'Medications used to treat bacterial infections like ampicillin, streptomycin, etc.'],
            ['name' => 'Antimicrobial Agents', 'description' => 'Substances that kill or inhibit the growth of microorganisms such as chloroform, sodium hypochlorite, etc.'],
            ['name' => 'Buffers', 'description' => 'Solutions used to maintain a stable pH in various biochemical processes like Tris buffer, EDTA, etc.'],
            ['name' => 'Chemical Solutions', 'description' => 'Various chemical solutions for laboratory use such as formamide, bromphenol blue, etc.'],
            ['name' => 'Culture Media Components', 'description' => 'Ingredients used to prepare culture media including agar powder, yeast extract, etc.'],
            ['name' => 'Detergents', 'description' => 'Cleaning agents like sodium dodecyl sulfate (SDS), Triton X, etc.'],
            ['name' => 'Enzymes', 'description' => 'Biological molecules that catalyze biochemical reactions like proteinase, amylase, etc.'],
            ['name' => 'Laboratory Consumables', 'description' => 'Disposable items used in laboratories such as tubes, pipette tips, etc.'],
            ['name' => 'Laboratory Equipment', 'description' => 'Various equipment used in laboratories like beakers, graduated cylinders, etc.'],
            ['name' => 'Nucleic Acid Stains', 'description' => 'Stains used to visualize nucleic acids in gel electrophoresis like ethidium bromide, Gel Red, etc.'],
            ['name' => 'PCR Reagents', 'description' => 'Reagents used in polymerase chain reaction (PCR) such as Taq DNA polymerase, dNTPs, etc.'],
            ['name' => 'Plasmids', 'description' => 'Circular DNA molecules used in molecular cloning like pLSLR, pTC217, etc.'],
            ['name' => 'Protein Products', 'description' => 'Products related to proteins such as albumin, BSA, etc.'],
            ['name' => 'Reagents', 'description' => 'Various reagents used in laboratory experiments and analysis like iodine, copper sulfate, etc.'],
            ['name' => 'Salts', 'description' => 'Inorganic compounds like sodium chloride, potassium chloride, etc.'],
            ['name' => 'Sterilization Products', 'description' => 'Products used for sterilizing equipment and surfaces like ethanol, zonrox, etc.'],
            ['name' => 'Storage Products', 'description' => 'Items used for storing laboratory samples and materials such as tubes, storage boxes, etc.'],
            ['name' => 'Sugars', 'description' => 'Carbohydrates including glucose, maltose, etc.'],
            ['name' => 'Tissue Culture Media', 'description' => 'Media used for growing and maintaining cell cultures like DMEM, RPMI, etc.'],
            ['name' => 'Water Treatment Products', 'description' => 'Products used for treating water in laboratory applications like sodium hydroxide, filter cartridges, etc.']
        ];

        foreach ($categories as $category) {
            Category::factory()->create($category);
        }
    }
}
