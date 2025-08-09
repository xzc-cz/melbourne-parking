<?php
/**
 * Minimal XLSX reader (first sheet) without external dependencies
 * Returns rows as array<array<string>>
 */

function xlsx_read_rows(string $filePath, int $maxRows = 0): array {
    if (!is_file($filePath)) {
        return [];
    }
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        return [];
    }

    // Load shared strings (optional)
    $sharedStrings = [];
    $ssIndex = $zip->locateName('xl/sharedStrings.xml');
    if ($ssIndex !== false) {
        $xml = @simplexml_load_string($zip->getFromIndex($ssIndex));
        if ($xml && isset($xml->si)) {
            foreach ($xml->si as $si) {
                // join t nodes
                $texts = [];
                if (isset($si->t)) {
                    $texts[] = (string)$si->t;
                }
                if (isset($si->r)) {
                    foreach ($si->r as $run) {
                        if (isset($run->t)) $texts[] = (string)$run->t;
                    }
                }
                $sharedStrings[] = implode('', $texts);
            }
        }
    }

    // Resolve first worksheet path via workbook.rels
    $sheetPath = 'xl/worksheets/sheet1.xml';
    $wbIdx = $zip->locateName('xl/workbook.xml');
    $relsIdx = $zip->locateName('xl/_rels/workbook.xml.rels');
    if ($wbIdx !== false && $relsIdx !== false) {
        $wb = @simplexml_load_string($zip->getFromIndex($wbIdx));
        $rels = @simplexml_load_string($zip->getFromIndex($relsIdx));
        $namespaces = $wb ? $wb->getDocNamespaces(true) : [];
        $sheets = $wb && isset($wb->sheets) ? $wb->sheets->sheet : [];
        if ($sheets) {
            $first = $sheets[0];
            $ridAttr = $first->attributes($namespaces['r'] ?? null);
            $rid = (string)($ridAttr['id'] ?? '');
            if ($rid && $rels && isset($rels->Relationship)) {
                foreach ($rels->Relationship as $rel) {
                    if ((string)$rel['Id'] === $rid) {
                        $target = (string)$rel['Target'];
                        if ($target) {
                            // Target is relative to xl/
                            $sheetPath = 'xl/' . ltrim($target, '/');
                        }
                        break;
                    }
                }
            }
        }
    }

    $sheetIdx = $zip->locateName($sheetPath);
    if ($sheetIdx === false) {
        $zip->close();
        return [];
    }
    $sheetXml = @simplexml_load_string($zip->getFromIndex($sheetIdx));
    $zip->close();
    if (!$sheetXml) return [];

    // Iterate rows
    $rows = [];
    $rowCount = 0;
    foreach ($sheetXml->sheetData->row as $row) {
        $cells = [];
        foreach ($row->c as $c) {
            $t = (string)$c['t'];
            $v = isset($c->v) ? (string)$c->v : '';
            if ($t === 's') {
                // shared string
                $idx = is_numeric($v) ? (int)$v : -1;
                $cells[] = ($idx >= 0 && isset($sharedStrings[$idx])) ? $sharedStrings[$idx] : '';
            } elseif ($t === 'inlineStr') {
                $cells[] = isset($c->is->t) ? (string)$c->is->t : '';
            } else {
                $cells[] = $v;
            }
        }
        $rows[] = $cells;
        $rowCount++;
        if ($maxRows > 0 && $rowCount >= $maxRows) break;
    }
    return $rows;
}

?>


