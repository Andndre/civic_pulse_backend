<?php

namespace App\Services;

use SimpleXMLElement;
use ZipArchive;

class SimpleXlsxReader
{
    /**
     * Parse a basic Excel (.xlsx) file and return an array of rows mapped by header names.
     *
     * @throws \Exception
     */
    public static function read(string $filePath): array
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            throw new \Exception("Cannot open file: {$filePath}");
        }

        // 1. Read shared strings
        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml) {
            $xml = new SimpleXMLElement($stringsXml);
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string) $si->t;
                } elseif (isset($si->r)) {
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string) $r->t;
                    }
                    $sharedStrings[] = $text;
                } else {
                    $sharedStrings[] = '';
                }
            }
        }

        // 2. Read sheet1
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (! $sheetXml) {
            $zip->close();
            throw new \Exception('Invalid Excel template worksheet structure');
        }

        $xml = new SimpleXMLElement($sheetXml);
        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $rowNum = (int) $row['r'];
            $rowData = [];

            foreach ($row->c as $c) {
                $ref = (string) $c['r']; // e.g. "A1"
                $col = preg_replace('/[0-9]/', '', $ref); // e.g. "A"

                $value = '';
                if (isset($c->v)) {
                    $v = (string) $c->v;
                    if (isset($c['t']) && (string) $c['t'] === 's') {
                        $value = $sharedStrings[(int) $v] ?? '';
                    } else {
                        $value = $v;
                    }
                }
                $rowData[$col] = trim($value);
            }
            $rows[$rowNum] = $rowData;
        }

        $zip->close();

        if (empty($rows)) {
            return [];
        }

        ksort($rows);
        $firstRowIndex = array_key_first($rows);
        $headers = $rows[$firstRowIndex];

        $result = [];
        foreach ($rows as $rowIndex => $rowData) {
            if ($rowIndex === $firstRowIndex) {
                continue;
            }

            $mapped = [];
            foreach ($headers as $colLetter => $headerName) {
                $mapped[trim(strtolower($headerName))] = $rowData[$colLetter] ?? '';
            }
            // Keep index to help trace rows in errors
            $mapped['__row_index__'] = $rowIndex;
            $result[] = $mapped;
        }

        return $result;
    }
}
