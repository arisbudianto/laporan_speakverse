<?php

namespace App\Support;

use RuntimeException;

class SimpleXlsxReader
{
    /**
     * @return list<array<int, string>>
     */
    public static function rows(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException('File Excel tidak dapat dibaca.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File bukan Excel .xlsx yang valid.');
        }

        $shared = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml) {
            $sx = simplexml_load_string($sharedXml);
            if ($sx) {
                foreach ($sx->si as $si) {
                    $shared[] = trim(html_entity_decode((string) $si->t, ENT_QUOTES | ENT_XML1, 'UTF-8'));
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! $sheetXml) {
            throw new RuntimeException('Lembar pertama Excel tidak ditemukan.');
        }

        $sheet = simplexml_load_string($sheetXml);
        if (! $sheet) {
            throw new RuntimeException('Isi Excel rusak.');
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                $col = self::columnIndex($ref);
                $type = (string) $cell['t'];
                $value = (string) $cell->v;
                if ($type === 's') {
                    $value = $shared[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) $cell->is->t;
                }
                $values[$col] = trim($value);
            }
            if ($values === []) {
                continue;
            }
            ksort($values);
            $max = max(array_keys($values));
            $line = [];
            for ($i = 1; $i <= $max; $i++) {
                $line[] = $values[$i] ?? '';
            }
            if (implode('', $line) === '') {
                continue;
            }
            $rows[] = $line;
        }

        return $rows;
    }

    private static function columnIndex(string $cellRef): int
    {
        preg_match('/^[A-Z]+/', strtoupper($cellRef), $match);
        $letters = $match[0] ?? 'A';
        $n = 0;
        foreach (str_split($letters) as $ch) {
            $n = ($n * 26) + (ord($ch) - 64);
        }

        return $n;
    }
}
