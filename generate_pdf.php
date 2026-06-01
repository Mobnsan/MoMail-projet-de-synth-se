<?php
require 'vendor/autoload.php';

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use Dompdf\Dompdf;
use Dompdf\Options;

// Read Markdown
$mdContent = file_get_contents('C:\Users\zahra\.gemini\antigravity\brain\881fc499-ffea-4365-9ce8-818c16f0d43f\cahier_des_charges.md');

// Convert absolute file:/// paths to real paths for Dompdf
$mdContent = str_replace('file:///c:/', 'C:/', $mdContent);
$mdContent = str_replace('file:///C:/', 'C:/', $mdContent);

// Convert Markdown to HTML
$environment = new Environment([]);
$environment->addExtension(new CommonMarkCoreExtension());
$environment->addExtension(new TableExtension());

$converter = new MarkdownConverter($environment);
$htmlBody = $converter->convert($mdContent)->getContent();

// Build final HTML
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: "Helvetica", sans-serif; font-size: 12pt; line-height: 1.5; color: #333; }
        h1, h2, h3 { color: #0056b3; }
        h1 { font-size: 24pt; margin-top: 0; }
        h2 { font-size: 18pt; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 30px; }
        h3 { font-size: 14pt; }
        a { color: #0056b3; text-decoration: none; }
        img { max-width: 100%; height: auto; }
        .text-center { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        hr { border: 0; border-top: 1px solid #eee; margin: 20px 0; }
    </style>
</head>
<body>
    ' . $htmlBody . '
</body>
</html>
';

// Setup Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Needed for absolute paths

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output PDF
$pdfPath = 'C:\Users\zahra\.gemini\antigravity\brain\881fc499-ffea-4365-9ce8-818c16f0d43f\Cahier_des_Charges_MoMail.pdf';
file_put_contents($pdfPath, $dompdf->output());

echo "PDF generated successfully at: " . $pdfPath . "\n";
