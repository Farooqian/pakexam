<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\TextAlign;

$phpWord = new PhpWord();
$section = $phpWord->addSection();

// Adding a text with center alignment
$section->addText(
    'Hello world!',
    array('alignment' => TextAlign::CENTER)  // Center-aligned text
);

// Save the document
$phpWord->save('example.docx', 'Word2007');
?>
