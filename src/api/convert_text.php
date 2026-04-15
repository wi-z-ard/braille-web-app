<?php
declare(strict_types=1);

Security::requireAuth();

$data = json_decode(file_get_contents('php://input'), true);
$text = trim($data['text'] ?? '');
$direction = $data['direction'] ?? 'to_braille';
$language = $data['language'] ?? 'auto';
$format = $data['format'] ?? 'unicode';

$maxChars = Settings::getInt('text_conversion_limit', 5000);

if (!$text || strlen($text) > $maxChars) {
    Security::jsonResponse(['error' => "Text must be 1-{$maxChars} characters"], 400);
}

if (!in_array($direction, ['to_braille', 'from_braille'], true)) {
    Security::jsonResponse(['error' => 'Invalid direction'], 400);
}

if (!in_array($language, ['auto', 'en', 'ar'], true)) {
    Security::jsonResponse(['error' => 'Invalid language'], 400);
}

if (!in_array($format, ['unicode', 'brf'], true)) {
    Security::jsonResponse(['error' => 'Invalid format'], 400);
}

try {
    $result = convertText($text, $direction, $language, $format);
    Security::jsonResponse(['result' => $result]);
} catch (Exception $e) {
    Security::jsonResponse(['error' => $e->getMessage()], 500);
}

function convertText(string $text, string $direction, string $lang, string $format): string {
    // Auto-detect language
    if ($lang === 'auto') {
        $arabicChars = preg_match_all('/[\x{0600}-\x{06FF}]/u', $text);
        $lang = $arabicChars > strlen($text) * 0.2 ? 'ar' : 'en';
    }

    $table = $lang === 'ar' ? 'ar-ar-g1.utb' : 'en-ueb-g2.ctb';

    if ($direction === 'to_braille') {
        return textToBraille($text, $table, $format);
    } else {
        return brailleToText($text, $table);
    }
}

function textToBraille(string $text, string $table, string $format): string {
    $displayTable = $format === 'unicode' ? 'unicode.dis,' : '';
    $fullTable = $displayTable . $table;

    $process = proc_open(
        ['lou_translate', $fullTable],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes
    );

    if (!is_resource($process)) {
        throw new Exception('Failed to start conversion process');
    }

    fwrite($pipes[0], $text);
    fclose($pipes[0]);

    $result = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    return trim($result);
}

function brailleToText(string $braille, string $table): string {
    $process = proc_open(
        ['lou_translate', '--backward', $table],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes
    );

    if (!is_resource($process)) {
        throw new Exception('Failed to start conversion process');
    }

    fwrite($pipes[0], $braille);
    fclose($pipes[0]);

    $result = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    return trim($result);
}
