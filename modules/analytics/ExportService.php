<?php
/**
 * Analytics Export Service
 * Generates PDF and Excel exports for analytics reports
 * Supports all report types (dashboard, payroll, HMO, compensation, etc.)
 */

require_once __DIR__ . '/AnalyticsService.php';

class ExportService
{
    private $analytics;
    private $exportDir;

    public function __construct()
    {
        $this->analytics = new AnalyticsService();
        $this->exportDir = __DIR__ . '/exports';
        
        // Create exports directory if it doesn't exist
        if (!is_dir($this->exportDir)) {
            mkdir($this->exportDir, 0755, true);
        }
    }

    /**
     * Main export method
     */
    public function export($reportType, $format, $options = [])
    {
        $fileName = $this->generateFileName($reportType, $format);
        
        switch ($format) {
            case 'pdf':
                return $this->exportPDF($reportType, $fileName, $options);
            case 'excel':
                return $this->exportExcel($reportType, $fileName, $options);
            case 'csv':
                return $this->exportCSV($reportType, $fileName, $options);
            default:
                throw new Exception('Unsupported export format: ' . $format);
        }
    }

    /**
     * Generate PDF export
     */
    private function exportPDF($reportType, $fileName, $options)
    {
        // For now, create CSV which can be converted to PDF
        // In production, use a library like mPDF or TCPDF
        $csvFile = str_replace('.pdf', '.csv', $fileName);
        $this->exportCSV($reportType, $csvFile, $options);
        
        // Return the CSV filename (can be enhanced with proper PDF library later)
        return str_replace('.csv', '.pdf', $csvFile);
    }

    /**
     * Generate Excel export
     */
    private function exportExcel($reportType, $fileName, $options)
    {
        // Simple CSV export (Excel-compatible)
        return $this->exportCSV($reportType, str_replace('.xlsx', '.csv', $fileName), $options);
    }

    /**
     * Generate CSV export
     */
    private function exportCSV($reportType, $fileName, $options)
    {
        $filePath = $this->exportDir . '/' . $fileName;
        $handle = fopen($filePath, 'w');

        if (!$handle) {
            throw new Exception('Unable to create export file');
        }

        // Add BOM for UTF-8 Excel compatibility
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Generate content based on report type
        $data = $this->getReportData($reportType, $options);
        
        // Write headers
        if (!empty($data)) {
            $firstRow = reset($data);
            fputcsv($handle, array_keys($firstRow));
            
            // Write data rows
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
        }

        fclose($handle);
        return $fileName;
    }

    /**
     * Get report data
     */
    private function getReportData($reportType, $options)
    {
        $department = $options['department'] ?? null;
        $startDate = $options['startDate'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $options['endDate'] ?? date('Y-m-d');

        switch ($reportType) {
            case 'payroll-trends':
                $trends = $this->analytics->getMonthlyPayrollTrends($department);
                return $this->formatForExport($trends);

            case 'compensation-analysis':
                $data = $this->analytics->getCostByDepartment();
                return $this->formatForExport($data);

            case 'headcount-analytics':
                $data = $this->analytics->getHeadcountByDepartment();
                return $this->formatForExport($data);

            case 'hmo-insights':
                $enrollments = $this->analytics->getProviderAnalysis();
                return $this->formatForExport($enrollments);

            case 'movement-analytics':
                $data = [
                    'joiners' => $this->analytics->getMovementByType('joining', 30, $department),
                    'leavers' => $this->analytics->getMovementByType('termination', 30, $department)
                ];
                return $this->formatForExport($data);

            case 'cost-analysis':
                $costData = [
                    [
                        'category' => 'Payroll Cost',
                        'amount' => $this->analytics->getTotalPayrollCost(30)['gross_total'] ?? 0
                    ],
                    [
                        'category' => 'HMO Cost',
                        'amount' => $this->analytics->getHMOTotalCost()
                    ]
                ];
                return $this->formatForExport($costData);

            case 'employee-master':
                $report = $this->analytics->getEmployeeMasterReport($department, null, null, 1000, 0);
                return $this->formatForExport($report);

            case 'payroll-summary':
                $report = $this->analytics->getPayrollSummaryReport($startDate, $endDate, $department, 1000, 0);
                return $this->formatForExport($report);

            case 'dashboard':
                // Create a summary dashboard export
                $summary = [
                    [
                        'metric' => 'Total Headcount',
                        'value' => $this->analytics->getHeadcountSummary($department)['total'] ?? 0
                    ],
                    [
                        'metric' => 'Total Payroll Cost (30 days)',
                        'value' => $this->analytics->getTotalPayrollCost(30)['gross_total'] ?? 0
                    ],
                    [
                        'metric' => 'HMO Enrollment Rate',
                        'value' => $this->analytics->getHMOEnrollmentRate() . '%'
                    ],
                    [
                        'metric' => 'Total HMO Cost',
                        'value' => $this->analytics->getHMOTotalCost()
                    ]
                ];
                return $this->formatForExport($summary);

            default:
                throw new Exception('Unknown report type: ' . $reportType);
        }
    }

    /**
     * Format data for export
     */
    private function formatForExport($data)
    {
        if (!is_array($data)) {
            return [];
        }

        // Flatten nested arrays
        $flattened = [];
        foreach ($data as $item) {
            if (is_array($item)) {
                $flattened[] = $this->flattenArray($item);
            } else {
                $flattened[] = [];
            }
        }

        return $flattened ?: [];
    }

    /**
     * Flatten nested array
     */
    private function flattenArray($array, $prefix = '')
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '_' . $key : $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }

    /**
     * Generate unique filename
     */
    private function generateFileName($reportType, $format)
    {
        $timestamp = date('YmdHis');
        return "{$reportType}_{$timestamp}.{$format}";
    }

    /**
     * Get all exports
     */
    public function getExports()
    {
        if (!is_dir($this->exportDir)) {
            return [];
        }

        $files = [];
        foreach (scandir($this->exportDir) as $file) {
            if ($file !== '.' && $file !== '..') {
                $files[] = [
                    'name' => $file,
                    'size' => filesize($this->exportDir . '/' . $file),
                    'created' => filemtime($this->exportDir . '/' . $file),
                    'url' => '/modules/analytics/exports/' . $file
                ];
            }
        }

        return array_reverse($files); // Most recent first
    }

    /**
     * Delete old exports (more than 7 days old)
     */
    public function cleanupOldExports($daysOld = 7)
    {
        if (!is_dir($this->exportDir)) {
            return;
        }

        $cutoffTime = time() - ($daysOld * 24 * 3600);
        foreach (scandir($this->exportDir) as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $this->exportDir . '/' . $file;
                if (filemtime($filePath) < $cutoffTime) {
                    unlink($filePath);
                }
            }
        }
    }
}
?>