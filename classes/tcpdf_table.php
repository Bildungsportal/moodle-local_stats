<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local_stats
 * @copyright  2024 Austrian Federal Ministry of Education
 * @author     GTN solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stats;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/pdflib.php");
// extend TCPF with custom functions
class tcpdf_table extends \TCPDF {
    public function ColoredTable(array $header, array $data, $pagewidth = 190) {
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');

        // Lengths
        $ls = [ 0 ];
        // Get the largest heading
        for ($i = 0; $i < count($header); $i++) {
            if (strlen($header[$i]) > $ls[$i]) {
                $ls[$i] = strlen($header[$i]);
            }
        }
        //Get the largest value in each column
        foreach($data as $row) {
            for($i = 0; $i < count($header); ++$i) {
                if (empty($ls[$i])) $ls[$i] = 0;
                if (strlen($row[$i]) > $ls[$i]) {
                    $ls[$i] = strlen($row[$i]);
                }
            }
        }
        $total = array_sum($ls);
        // Avoid a "division by zero"-exception
        if ($total == 0) {
            $total = 1;
        }
        for ($i = 0; $i < count($ls); $i++) {
            $ls[$i] = $pagewidth/$total*$ls[$i];
        }

        // Header
        for($i = 0; $i < count($header); ++$i) {
            $this->Cell($ls[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        $fill = 0;
        foreach($data as $row) {
            for($i = 0; $i < count($header); ++$i) {
                if ($i == 0) {
                    $this->Cell($ls[$i], 6, $row[$i], 'LR', 0, 'C', $fill);
                } else {
                    if (is_number($row[$i])) {
                        $this->Cell($ls[$i], 6, number_format($row[$i]), 'LR', 0, 'R', $fill);
                    } else {
                        $this->Cell($ls[$i], 6, $row[$i], 'LR', 0, 'C', $fill);
                    }
                }

            }
            $this->Ln();
            $fill=!$fill;
        }
        // Ending line
        $this->Cell(array_sum($ls), 0, '', 'T');
    }
}
