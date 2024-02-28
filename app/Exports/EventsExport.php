<?php

namespace App\Exports;

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EventsExport implements FromQuery, WithHeadings
{
  use Exportable;

  private $filters;

  public function __construct($filters)
  {
    $this->filters = [...$filters, "queryForExport" => true, "selectAttributes" => [
        "deviceSerialNumber",
        "event_types.key",
        "score",
        "criticalityLevel",
        "detectionDate",
        "description",
      ]
    ];
  }

  public function query()
  {
    return (new EventController())->list($this->filters);
  }

  public function headings(): array
  {
    return ["Serial Number", "Notification Type", "Score", "Criticality Level", "Detection Date", "Description"];
  }
}
