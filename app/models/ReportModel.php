<?php
class ReportModel {
    public function getFilteredReports() {
        return [
            ["id" => 1, "usuario" => "Alice", "monto" => "10.00", "fecha" => "2025-08-01"],
            ["id" => 2, "usuario" => "Bob", "monto" => "25.50", "fecha" => "2025-08-05"],
            ["id" => 3, "usuario" => "Charlie", "monto" => "5.75", "fecha" => "2025-08-10"]
        ];
    }
}
?>
