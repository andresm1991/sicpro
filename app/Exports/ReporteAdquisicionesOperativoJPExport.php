<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class ReporteAdquisicionesOperativoJPExport implements FromCollection, WithEvents, WithColumnWidths
{
    protected $reportData;
    protected $grandTotal;
    protected $currentRow = 0;
    protected $categoryRows = [];
    public function __construct(array $reportData, float $grandTotal)
    {
        $this->reportData = $reportData;
        $this->grandTotal = $grandTotal;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $collection = new Collection();
        $this->currentRow = 1; // Empezamos en la fila 1

        $collection->push(['Reporte de Adquisiciones']);
        $collection->push(['Fecha de Generación: ' . now()->format('d/m/Y')]);
        $this->currentRow += 2;

        foreach ($this->reportData as $categoria => $items) {
            $collection->push(['']); // Fila en blanco para separar
            $this->currentRow++;

            // Guardamos la fila donde empieza el título de la categoría
            $this->categoryRows[$categoria]['title'] = $this->currentRow;
            $collection->push([$categoria]);
            $this->currentRow++;

            // Guardamos la fila donde empiezan las cabeceras
            $this->categoryRows[$categoria]['headers'] = $this->currentRow;

            switch ($categoria) {
                case 'Mano de Obra':
                    $collection->push(['Proyecto', 'Tipo', 'Fecha Desde', 'Fecha Hasta', 'Total']);
                    break;
                case 'Contratistas':
                    $collection->push(['Proyecto', 'Proveedor', 'Categoría', 'Total Contratado', 'Total Pagado', 'Saldo']);
                    break;
                default: // Adquisiciones
                    $collection->push(['Fecha', 'Número', 'Proyecto', 'Proveedor', 'Estado', 'Factura', 'Forma Pago', 'Necesidades', 'Total']);
                    break;
            }
            $this->currentRow++;

            if (empty($items)) {
                $collection->push(['No se encontraron registros para esta categoría.']);
                $this->currentRow++;
                continue;
            }

            $totalCategoria = 0;
            foreach ($items as $item) {
                $rowData = [];
                switch ($categoria) {
                    case 'Mano de Obra':
                        $rowData = [
                            $item['proyecto']['nombre_proyecto'] ?? 'N/A',
                            $item['tipo'],
                            \Carbon\Carbon::parse($item['fecha_desde'])->format('d/m/Y'),
                            \Carbon\Carbon::parse($item['fecha_hasta'])->format('d/m/Y'),
                            floatval($item['total_recibir']),
                        ];
                        $totalCategoria += $item['total_recibir'];
                        break;
                    case 'Contratistas':
                        $rowData = [
                            $item['proyecto']['nombre_proyecto'] ?? 'N/A',
                            $item['proveedor']['razon_social'] ?? 'N/A',
                            $item['categoria_proveedor']['descripcion'] ?? 'N/A',
                            floatval($item['total_contratado']),
                            floatval($item['total_pagado']),
                            floatval($item['total_pendiente']),
                        ];
                        $totalCategoria += $item['total_contratado']; // O el total que corresponda
                        break;
                    default: // Adquisiciones
                        $rowData = [
                            \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y'),
                            $item['numero'],
                            $item['proyecto']['nombre_proyecto'] ?? 'N/A',
                            $item['proveedor']['razon_social'] ?? 'N/A',
                            $item['estado'],
                            (string)$item['nro_factura'] ?? 'N/A',
                            $item['forma_pago']['descripcion'] ?? 'N/A',
                            collect($item['detalles'])->pluck('necesidad')->implode(', '),
                            floatval($item['total_general']),
                        ];
                        $totalCategoria += $item['total_general'];
                        break;
                }
                $collection->push($rowData);
                $this->currentRow++;
            }

            // Fila de Total por categoría
            $totalRow = $this->createTotalRow($categoria, collect($items));
            $collection->push($totalRow);
            $this->categoryRows[$categoria]['total'] = $this->currentRow;
            $this->currentRow++;
        }

        // Gran Total al final
        $collection->push(['']);
        $collection->push(['', '', '', '', '', '', '', 'TOTAL GENERAL:', $this->grandTotal]);
        $this->currentRow += 2;
        $this->categoryRows['grand_total'] = $this->currentRow - 1;


        return $collection;
    }

    private function createTotalRow($categoria, Collection $items): array
    {
        switch ($categoria) {
            case 'Mano de Obra':
                return [
                    '', // A
                    '', // B
                    '', // C
                    'Total:', // D
                    $items->sum('total_recibir'), // E
                ];
            case 'Contratistas':
                return [
                    '', // A
                    '', // B
                    'Subtotales:', // C
                    $items->sum('total_contratado'), // D
                    $items->sum('total_pagado'), // E
                    $items->sum('total_pendiente'), // F
                ];
            default:
                return [
                    '', // A
                    '', // B
                    '', // C
                    '', // D
                    '', // E
                    '', // F
                    '', // G
                    'Total:', // H
                    $items->sum('total_general'), // I
                ];
        }
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 15,
            'C' => 30,
            'D' => 30,
            'E' => 15,
            'F' => 15,
            'G' => 20,
            'H' => 40,
            'I' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $formatoCuatroDecimales = '$ #,##0.0000;[Red]-$ #,##0.0000;$ #,##0.0000';
                $formatoTexto = NumberFormat::FORMAT_TEXT; // Es equivalente a usar '@'


                // Estilo general
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

                // Título del Reporte
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->mergeCells('A2:I2');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];

                $totalStyle = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ];

                foreach ($this->categoryRows as $categoria => $rows) {
                    if ($categoria === 'grand_total') continue;

                    // Estilo del Título de la Categoría
                    $sheet->mergeCells('A' . $rows['title'] . ':I' . $rows['title']);
                    $sheet->getStyle('A' . $rows['title'])->getFont()->setBold(true)->setSize(12);
                    $sheet->getStyle('A' . $rows['title'])->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');

                    // Estilo de las Cabeceras
                    $headerRange = 'A' . $rows['headers'] . ':' . $this->getHeaderEndColumn($categoria) . $rows['headers'];
                    $sheet->getStyle($headerRange)->applyFromArray($headerStyle);

                    // Estilo del Total de la categoría
                    $totalRange = 'A' . $rows['total'] . ':' . $this->getHeaderEndColumn($categoria) . $rows['total'];
                    $sheet->getStyle($totalRange)->applyFromArray($totalStyle);
                    $sheet->getStyle($this->getTotalColumn($categoria) . $rows['total'])->getNumberFormat()->setFormatCode($formatoCuatroDecimales);
                }

                // Estilo Gran Total
                $grandTotalRow = $this->categoryRows['grand_total'];
                $sheet->getStyle('H' . $grandTotalRow . ':I' . $grandTotalRow)->applyFromArray($totalStyle);
                $sheet->getStyle('I' . $grandTotalRow)->getNumberFormat()->setFormatCode($formatoCuatroDecimales);

                // Formato de moneda a las columnas de totales
                // (Esto es un ejemplo, puedes necesitar ajustar los rangos)
                $lastRow = $this->currentRow - 1;
                $sheet->getStyle('D3:E' . $lastRow)->getNumberFormat()->setFormatCode($formatoCuatroDecimales);
                // Columna I para Adquisiciones.
                $sheet->getStyle('I3:I' . $lastRow)->getNumberFormat()->setFormatCode($formatoCuatroDecimales);

                // SEGUNDO, manejamos la columna F, que es mixta (contiene Saldo y Factura).
                // Por defecto, la formateamos como moneda para el 'Saldo' de los Contratistas.
                $sheet->getStyle('F3:F' . $lastRow)->getNumberFormat()->setFormatCode($formatoCuatroDecimales);

                // TERCERO Y MÁS IMPORTANTE: Recorremos las categorías de nuevo y aplicamos el formato
                // de TEXTO a la columna 'F' solo para las filas que corresponden a Adquisiciones.
                // ESTO SOBREESCRIBE el formato de moneda que pusimos antes, pero solo donde es necesario.
                foreach ($this->categoryRows as $categoria => $rows) {
                    if ($categoria !== 'Contratistas' && $categoria !== 'Mano de Obra' && $categoria !== 'grand_total' && isset($rows['headers'])) {
                        $startRow = $rows['headers'] + 1;
                        $endRow = $rows['total'] - 1;
                        if ($startRow <= $endRow) {
                            $sheet->getStyle("F{$startRow}:F{$endRow}")->getNumberFormat()->setFormatCode($formatoTexto);
                        }
                    }
                }
            },
        ];
    }

    private function getHeaderEndColumn(string $categoria): string
    {
        switch ($categoria) {
            case 'Mano de Obra':
                return 'E';
            case 'Contratistas':
                return 'F';
            default:
                return 'I';
        }
    }

    private function getTotalColumn(string $categoria): string
    {
        switch ($categoria) {
            case 'Mano de Obra':
                return 'E';
            case 'Contratistas':
                return 'D';
            default:
                return 'I';
        }
    }
}
