<?php
$file = 'RequisitosSites.docx';
$zip = new ZipArchive;
if ($zip->open($file) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    // simple regex to extract text within <w:t> tags
    preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/i', $xml, $matches);
    echo implode(' ', $matches[1]);
} else {
    echo "Failed";
}
?>
