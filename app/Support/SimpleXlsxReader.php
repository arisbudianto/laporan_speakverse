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
        return self::parse($path, null);
    }

    /**
     * Baca satu sheet tertentu berdasarkan nama tab (sesuai yang terlihat
     * di Excel), bukan selalu sheet pertama secara fisik di file .xlsx.
     * Urutan fisik sheet1.xml/sheet2.xml di dalam file tidak selalu sama
     * dengan urutan tab yang terlihat, jadi nama harus dipetakan lewat
     * xl/workbook.xml + xl/_rels/workbook.xml.rels.
     *
     * @return list<array<int, string>>
     */
    public static function rowsFromSheet(string $path, string $sheetName): array
    {
        return self::parse($path, $sheetName);
    }

    /**
     * @return list<string>  nama tab sheet sesuai urutan di Excel
     */
    public static function sheetNames(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException('File Excel tidak dapat dibaca.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File bukan Excel .xlsx yang valid.');
        }

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $zip->close();

        if (! $workbookXml) {
            throw new RuntimeException('workbook.xml tidak ditemukan di file Excel.');
        }

        $workbook = simplexml_load_string($workbookXml);
        if (! $workbook) {
            throw new RuntimeException('Isi Excel rusak.');
        }

        $names = [];
        foreach ($workbook->sheets->sheet as $sheet) {
            $names[] = (string) $sheet['name'];
        }

        return $names;
    }

    /**
     * @return list<array<int, string>>
     */
    private static function parse(string $path, ?string $sheetName): array
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

        $sheetTarget = 'worksheets/sheet1.xml';

        if ($sheetName !== null) {
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
            if (! $workbookXml || ! $relsXml) {
                $zip->close();
                throw new RuntimeException('Struktur workbook Excel tidak lengkap.');
            }

            $workbook = simplexml_load_string($workbookXml);
            $rels = simplexml_load_string($relsXml);
            if (! $workbook || ! $rels) {
                $zip->close();
                throw new RuntimeException('Isi Excel rusak.');
            }

            $rId = null;
            foreach ($workbook->sheets->sheet as $sheet) {
                if ((string) $sheet['name'] === $sheetName) {
                    $rId = (string) $sheet->attributes('r', true)->id;
                    break;
                }
            }

            if ($rId === null) {
                $zip->close();
                throw new RuntimeException("Sheet \"{$sheetName}\" tidak ditemukan di file Excel.");
            }

            $target = null;
            foreach ($rels->Relationship as $rel) {
                if ((string) $rel['Id'] === $rId) {
                    $target = (string) $rel['Target'];
                    break;
                }
            }

            if ($target === null) {
                $zip->close();
                throw new RuntimeException("Sheet \"{$sheetName}\" tidak ditemukan di file Excel.");
            }

            // Target di rels relatif terhadap folder xl/, mis. "worksheets/sheet2.xml".
            $sheetTarget = ltrim($target, '/');
        }

        $sheetXml = $zip->getFromName('xl/' . $sheetTarget);
        $zip->close();

        if (! $sheetXml) {
            throw new RuntimeException('Lembar Excel tidak ditemukan.');
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
