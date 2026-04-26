<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PegawaiTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new PegawaiTemplateDataSheet(),
            new PegawaiTemplatePanduanSheet(),
        ];
    }
}
