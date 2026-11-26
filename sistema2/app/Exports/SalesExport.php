<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithFreezePane;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithEvents, WithFreezePane, WithHeadings, WithMapping, WithStyles
{
    private int $rowNumber = 0;

    public function __construct(private readonly array $filters) {}

    // Obtiene las ventas aplicando los mismos filtros y orden que el reporte.
    public function collection(): Collection
    {
        $query = Sale::query()->with(['customer', 'user']);

        if (! empty($this->filters['from'])) {
            $query->whereDate('date', '>=', $this->filters['from']);
        }

        if (! empty($this->filters['to'])) {
            $query->whereDate('date', '<=', $this->filters['to']);
        }

        if (! empty($this->filters['customer_id'])) {
            $query->where('customer_id', $this->filters['customer_id']);
        }

        if (! empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        if (! empty($this->filters['category_id'])) {
            $query->whereHas('details.medicine', function (Builder $medicineQuery) {
                $medicineQuery->where('category_id', $this->filters['category_id']);
            });
        }

        return $query->orderByDesc('date')->get();
    }

    public function headings(): array
    {
        return ['N.°', 'Fecha', 'Cliente', 'Vendedor', 'IGV', 'Total'];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0.00',
            'F' => '#,##0.00',
        ];
    }

    public function freezePane(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = $sheet->getHighestRow();
                $totalsRow = $lastDataRow + 1;

                $sheet->setCellValue("A{$totalsRow}", 'Totales');

                if ($lastDataRow > 1) {
                    $sheet->setCellValue("E{$totalsRow}", "=SUM(E2:E{$lastDataRow})");
                    $sheet->setCellValue("F{$totalsRow}", "=SUM(F2:F{$lastDataRow})");
                } else {
                    $sheet->setCellValue("E{$totalsRow}", 0);
                    $sheet->setCellValue("F{$totalsRow}", 0);
                }

                $sheet->getStyle("A{$totalsRow}:F{$totalsRow}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("E{$totalsRow}:F{$totalsRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle("E1:F{$totalsRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }

    public function map(mixed $row): array
    {
        if (! $row instanceof Sale) {
            throw new InvalidArgumentException('La exportación esperaba un registro de venta.');
        }

        return [
            ++$this->rowNumber,
            $row->date->format('d/m/Y H:i'),
            $row->customer->name,
            $row->user->name,
            (float) $row->igv,
            (float) $row->total,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
