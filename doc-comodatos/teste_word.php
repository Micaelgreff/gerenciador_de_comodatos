<?php

use PhpOffice\PhpWord\SimpleType\TextAlignment;

require_once __DIR__ . '/bootstrap.php';
require '../vendor/autoload.php';

// Creating the new document...
$phpWord = new \PhpOffice\PhpWord\PhpWord();

// Definindo qual vai ser o idioma do documento
$phpWord->getSettings()->setThemeFontLang(new PhpOffice\PhpWord\Style\Language('pt-br'));

// Definindo configurações padrões da fonte de todo documento
$phpWord->setDefaultFontSize(10);
$phpWord->setDefaultFontName('Arial');
//$phpWord->setDefaultParagraphStyle(array('alignment' => 'justify'));

// Criando uma seção
$section = $phpWord->addSection();
$sectionStyle = $section->getStyle(); // Parametro para alterar estilo do paragrafo

// Definindo a margem do documento para 1.27 cm
$sectionStyle->setMarginLeft(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.27));
$sectionStyle->setMarginRight(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.27));
$sectionStyle->setMarginTop(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.27));
$sectionStyle->setMarginBottom(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.27));

// Incluindo o conteúdo do documento ('linhas do texto')
$section->addTextBreak(1);
$phpWord->addTitleStyle(1, array('bold' => true, 'underline' => 'single'), array('alignment' => 'center'));
$section->addTitle("CONTRATO DE COMODATO DE APARELHO ELETRÔNICO", 1);

$section->addTextBreak(1);
$section->addText("IDENTIFICAÇÃO DAS PARTES CONTRATANTES", array('bold' => true));

$section->addTextBreak(1);
$textrun1 = $section->addTextRun();
$textrun1->addText("COMODANTE: REDEBRASIL GESTÃO DE ATIVOS LTDA, ", array('bold' => true));
$textrun1->addText("pessoa jurídica de direito privado, regularmente inscrita");

$textrun2 = $section->addTextRun();
$textrun2->addText("no CNPJ sob ");
$textrun2->addText("nº 03.681.777/0016-33 ", array('bold' => true));
$textrun2->addText("localizada em: ");
$textrun2->addText("Rua Protásio Alves, Nº47 ", array('bold' => true));
$textrun2->addText("– Bairro Niterói, Canoas – RS, CEP:");

$textrun3 = $section->addTextRun();
$textrun3->addText("90020-080. ");
$textrun3->addText("COMODATÁRIO: Gabriela Ajardo Rodrigues de Souza ", array('bold' => true));
$textrun3->addText("inscrito (a) no CPF sob nº ");
$textrun3->addText("852.860.520-53, ", array('bold' => true));

$textrun4 = $section->addTextRun();
$textrun4->addText("matrícula nº ");
$textrun4->addText("18149 ", array('bold' => true));
$textrun4->addText("sob o endereço ");
$textrun4->addText("Rua Minuano, 745 - Viamópolis Viamão • RS • CEP 94470250. ", array('bold' => true));

$section->addTextBreak(1);
$textrun5 = $section->addTextRun();
$textrun5->addText("As partes acima identificadas têm, por justo e acertado o presente ");
$textrun5->addText("Contrato de Comodato de Bem Móvel, ", array('bold' => true, 'underline' => 'single'));
$textrun5->addText("o qual se ");
$textrun5->addText("reger-se-á pelos artigos 579 e seguintes do Código Civil Brasileiro, pelas demais disposições legais aplicáveis à ");
$textrun5->addText("espécie, e especialmente pelas cláusulas e condições abaixo elencadas:");

$section->addTextBreak(1);
$textrun6 = $section->addTextRun();
$textrun6->addText("CLÁUSULA PRIMEIRA - ", array('bold' => true));
$textrun6->addText("O presente contrato tem como objetivo o empréstimo gratuito, pela ");
$textrun6->addText("COMODANTE, ", array('bold' => true));
$textrun6->addText("possuidora e proprietária do equipamento abaixo relacionado, ao(à) ");
$textrun6->addText("COMODATÁRIO(A) ", array('bold' => true));
$textrun6->addText("dos direitos de uso e gozo do objeto/aparelho eletrônico:");

$section->addTextBreak(1);
$textrun7 = $section->addTextRun();
$textrun7->addText("Aparelho:", array('bold' => true, 'underline' => 'single'));
$textrun7->addText(" Notebook Lenovo B330", array('bold' => true));

$textrun8 = $section->addTextRun();
$textrun8->addText("Patrimônio:", array('bold' => true, 'underline' => 'single'));
$textrun8->addText(" 15232", array('bold' => true));

$textrun9 = $section->addTextRun();
$textrun9->addText("Contendo:", array('bold' => true, 'underline' => 'single'));
$textrun9->addText(" Notebook, mouse, cabo de rede e carregador");

$textrun10 = $section->addTextRun();
$textrun10->addText("Valor do Bem:", array('bold' => true, 'underline' => 'single'));
$textrun10->addText(" R$ 3.740,00");

$section->addText("DAS OBRIGAÇÕES DO COMODATÁRIO", array('bold' => true));

$section->addTextBreak(1);
$textrun11 = $section->addTextRun();
$textrun11->addText("CLÁUSULA SEGUNDA – ", array('bold' => true));
$textrun11->addText("O(A) ");
$textrun11->addText("COMODATÁRIO(A) ", array('bold' => true));
$textrun11->addText("reconhece e declara que o equipamento ora cedido está em perfeitas condições de uso e funcionamento.");

$section->addTextBreak(1);
$textrun12 = $section->addTextRun();
$textrun12->addText("CLÁUSULA TERCEIRA – ", array('bold' => true));
$textrun12->addText("O objeto/aparelho eletrônico deverá ser utilizado ");
$textrun12->addText("única", array('bold' => true, 'underline' => 'single'));
$textrun12->addText(" e ");
$textrun12->addText("exclusivamente", array('bold' => true, 'underline' => 'single'));
$textrun12->addText(" a serviço da ");
$textrun12->addText("COMODANTE", array('bold' => true));
$textrun12->addText(", tendo em vista as atividade exercidas pelo(a) ");
$textrun12->addText("COMODATÁRIO(A)", array('bold' => true));
$textrun12->addText(", por decorrência de regular Contrato de Emprego regularmente firmado.");

$section->addTextBreak(1);
$textrun13 = $section->addTextRun();
$textrun13->addText("Parágrafo Primeiro", array('underline' => 'single'));
$textrun13->addText(" - A utilização do equipamento pelo(a) ");
$textrun13->addText("COMODATÁRIO(A)", array('bold' => true));
$textrun13->addText(", de forma diversa ao estabelecido neste Contrato, acarretará na imediata rescisão do presente instrumento, respondendo o(a) ");
$textrun13->addText("COMODATÁRIO(A)", array('bold' => true));
$textrun13->addText(" por perdas e danos, bem como pela indenização equivalente ao valor atualizado do bem, caso ocorra a deterioração e/ou a destruição do equipamento ou parte dele, por falta de conservação ou comprovado mau uso.");

$section->addTextBreak(1);
$textrun14 = $section->addTextRun();
$textrun14->addText("Parágrafo Segundo", array('underline' => 'single'));
$textrun14->addText(" - É proibido ao(à) ");
$textrun14->addText("COMODATÁRIO(A)", array('bold' => true));
$textrun14->addText(") emprestar o objeto do presente contrato a terceiros, assim como, utilizar o objeto para atividades alheias às decorrentes do Contrato de Emprego.");

$section->addTextBreak(1);
$textrun15 = $section->addTextRun();
$textrun15->addText("CLÁUSULA QUARTA", array('bold' => true));
$textrun15->addText(" - O(A) ");
$textrun15->addText("COMODATÁRIO(A) ", array('bold' => true));
$textrun15->addText(" deverá, ");
$textrun15->addText("de forma imediata", array('bold' => true, 'underline' => 'single'));
$textrun15->addText(", comunicar todo o qualquer problema/defeito que surgir no objeto do comodato, assim como, diligenciar, junto à ");
$textrun15->addText("COMODANTE", array('bold' => true));
$textrun15->addText(", o conserto e/ou troca do aparelho, observando, inclusive, eventuais prazos de garantia do objeto.");

$section->addPageBreak(); // Adicionando quebra de página

$section->addTextBreak(1);
$textrun16 = $section->addTextRun();
$textrun16->addText("Parágrafo Único", array('underline' => 'single'));
$textrun16->addText(" - O(A) ");
$textrun16->addText("COMODATÁRIO(A) ", array('bold' => true));
$textrun16->addText(" deverá restituir à ");
$textrun16->addText("COMODANTE", array('bold' => true));
$textrun16->addText(" o objeto/equipamento emprestado, de forma imediata e em perfeito estado de conservação, ressalvado o desgaste normal, natural e decorrente da utilização do aparelho, quando notificado para tal ato ou quando do término da relação de emprego.");

$section->addTextBreak(1);
$textrun17 = $section->addTextRun();
$textrun17->addText("CLÁUSULA QUINTA", array('bold' => true));
$textrun17->addText(" - O(A) ");
$textrun17->addText("COMODATÁRIO(A) ", array('bold' => true));
$textrun17->addText(" deverá fazer a devolução do objeto/equipamento ");
$textrun17->addText("em até 24(vinte e quatro) horas", array('italic' => true,'underline' => 'single'));
$textrun17->addText(", sob pena de, a partir de então, passar a se constituir em mora, se findo o Contrato de Emprego, ou requisitada a devolução do objeto/equipamento.");

$section->addTextBreak(1);
$textrun18 = $section->addTextRun();
$textrun18->addText("CLÁUSULA SEXTA", array('bold' => true));
$textrun18->addText(" - As situações não previstas nas Cláusulas anteriores serão regidas/dirimidas pela legislação pertinente e vigente ou regulados, posteriormente, pelas partes.");

$section->addTextBreak(1);
$textrun19 = $section->addTextRun();
$textrun19->addText("CLÁUSULA SÉTIMA", array('bold' => true));
$textrun19->addText(" - As partes contratantes elegem o foro da Comarca de ");
$textrun19->addText("Canoas / RS", array('bold' => true));
$textrun19->addText(", para dirimir quaisquer dúvidas e/ou questões e/ou litígios decorrentes do presente ajuste, renunciando, expressamente, qualquer outro foro, por mais privilegiado que seja.");

$section->addTextBreak(1);
$section->addText("Por estarem justos e contratados, as partes, após terem lido e dirimido eventuais dúvidas, assinam, sem qualquer constrangimento ou vício de vontade, o presente Contrato de Comodato, em 2(duas) vias de igual teor e forma, frente às testemunhas abaixo firmadas, para que do mesmo surtam seus jurídicos e legais efeitos.");

$section->addTextBreak(2);
$section->addText("Canoas, 31 de julho de 2022", null ,array('alignment' => 'right'));

$section->addTextBreak(1);
$section->addText("COMODANTE: _________________________________");

$section->addTextBreak(1);
$section->addText("COMODATÁRIO: _______________________________");

$section->addTextBreak(1);
$section->addText("Devolução: ____________________________________       Data: ___/___/_____");


/* $generator = new Picqer\Barcode\BarcodeGeneratorJPG();
file_put_contents('barcode.jpg', $generator->getBarcode('081231723897', $generator::TYPE_CODABAR));

$section->addImage("barcode.jpg"
    // This will output the barcode as HTML output to display in the browser
); */


// Saving the document as OOXML file...
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('Comodato.docx');

/* // Saving the document as ODF file...
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'ODText');
$objWriter->save('helloWorld.odt');

// Saving the document as HTML file...
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
$objWriter->save('helloWorld.html'); */

/* Note: we skip RTF, because it's not XML-based and requires a different example. */
/* Note: we skip PDF, because "HTML-to-PDF" approach is used to create PDF documents. */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
Documento 'Comodato.docx' criado na pasta /doc-comodatos
</body>
</html>