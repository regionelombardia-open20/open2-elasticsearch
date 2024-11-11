<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\admin\base
 * @category   CategoryName
 */

namespace open20\elasticsearch\utilities;

use Exception;
use Goutte\Client;
use open20\elasticsearch\models\ElasticIndex;
use open20\elasticsearch\models\NavItem;
use open20\elasticsearch\Module;
use Symfony\Component\DomCrawler\Crawler;
use Yii;
use yii\log\Logger;

class Utility {

    /**
     * Extensions allowed to be able to read the contents 
     * of the files to be indexed
     */
    const EXTENSIONS = [
        'pdf',
        'docx',
        'xlsx',
        'xls',
        'ods',
        'csv',
        'odt',
        'doc',
        'rtf',
        'xml',
        'txt'
    ];

    private $baseUrl = "";
    public $client = null;
    private $_crawler = null;
    public $index_settings_name;
    public $index_name;
    public $other_index;

    public function __construct() {
        $this->module = Module::instance();
        $this->baseUrl = \Yii::$app->params['platform']['frontendUrl'];
    }

    /**
     * 
     * @param NavItem $obj
     */
    public function rebuildCmsIndex($obj) {
        try {
            if ($obj->createUrlPreview()) {
                $path_string = $obj->elasticUrl;
                $this->module->attachElasticSearchBehavior($obj);

                $getCrawlerHtml = $this->getCrawlerHtml($this->baseUrl . "/" . $obj->getElastic_preview());

                if (!empty($getCrawlerHtml)) {
                    $obj->setElasticSourceText($getCrawlerHtml[0]);
                    $obj->setH1($getCrawlerHtml[1]);
                    $obj->setH2($getCrawlerHtml[2]);
                    $obj->setH3($getCrawlerHtml[3]);
                    $obj->setH4($getCrawlerHtml[4]);

                    $internalResponse = $this->client->getInternalResponse();
                    $method = 'getStatus';
                    if(method_exists($internalResponse,'getStatusCode')){
                        $method = 'getStatusCode';
                    }
                    if ($this->client->getInternalResponse()->$method() === 200) {
                        $obj->setElasticUrl($path_string);
                        $index = new ElasticIndex([
                            'model' => $obj
                        ]);
                        $index->save($index);
                    }
                }
            }
        } catch (Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
    }

    /**
     *
     * @param string $pageUrl
     * @return Response
     */
    protected function getCrawler($pageUrl) {
        try {
            $this->client = new Client();
            // $this->client->followRedirects(false);
            $this->_crawler = $this->client->request('GET', $pageUrl);

            $internalResponse = $this->client->getInternalResponse();
            $method = 'getStatus';
            if(method_exists($internalResponse,'getStatusCode')){
                $method = 'getStatusCode';
            }
            if ($this->client->getInternalResponse()->$method() !== 200) {
                $this->_crawler = false;
            }
        } catch (\Exception $e) {
            Yii::getLogger()->log($e->getMessage(), Logger::LEVEL_ERROR);
            $this->_crawler = false;
        }

        return $this->_crawler;
    }

    /**
     *
     * @param string $pageUrl
     * @return string
     */
    protected function getCrawlerHtml($pageUrl) {
        try {
            $this->other_index = null;
            $crawler = $this->getCrawler($pageUrl);

            if (!$crawler) {
                return '';
            }


            $crawler->filter('nav')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });
            $crawler->filter('footer')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });

            $crawler->filter('script')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });

            $crawler->filter('style')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });

            $crawler->filter('[class^="navbar"]')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });
            $crawler->filter('[id^="footer"]')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });
            $crawler->filter('[id^="header"]')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });
            $crawler->filter('.sr-only')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });

            if (empty($this->other_index['h1'])) {
                $this->other_index['h1'] = '';
            }
            if (empty($this->other_index['h2'])) {
                $this->other_index['h2'] = '';
            }
            if (empty($this->other_index['h3'])) {
                $this->other_index['h3'] = '';
            }
            if (empty($this->other_index['h4'])) {
                $this->other_index['h4'] = '';
            }
            $h1 = $crawler->filter('h1');
            $h2 = $crawler->filter('h2');
            $h3 = $crawler->filter('h3');
            $h4 = $crawler->filter('h4');

            $h1->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $this->other_index['h1'] = $this->other_index['h1'] . $node->textContent . ' ';
                }
            });
            $h2->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $this->other_index['h2'] = $this->other_index['h2'] . $node->textContent . ' ';
                }
            });
            $h3->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $this->other_index['h3'] = $this->other_index['h3'] . $node->textContent . ' ';
                }
            });
            $h4->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $this->other_index['h4'] = $this->other_index['h4'] . $node->textContent . ' ';
                }
            });

            $elasticSearchModule = Yii::$app->getModule('elasticsearch');
            $cssFilter = $elasticSearchModule->cssGetCrawlerHtmlFilter;

            return [preg_replace('/\s\s+/', ' ', strip_tags($crawler->filter($cssFilter)->html())),
                $this->other_index['h1'], $this->other_index['h2'], $this->other_index['h3'], $this->other_index['h4']];
        } catch (\Exception $e) {
            return '';
        }
    }

    public static function getTextFromFile($path, $extension = null) {
        $text = '';
        try {

            if (file_exists($path)) {
                if (empty($extension)) {
                    $filename = \yii\helpers\StringHelper::basename($path);
                    $pos = strpos(\yii\helpers\StringHelper::basename($path), '.');
                    $extension = substr($filename, $pos + 1);
                }
                $ext = strtolower($extension);
                switch ($ext) {
                    case 'pdf':
                        $text = self::getTextFromPdf($path);
                        break;
                    case 'docx':
                        $text = self::getTextFromDocx($path);
                        break;
                    case 'xlsx':
                        $text = self::getTextFromXlsx($path);
                        break;
                    case 'xls':
                        $text = self::getTextFromXls($path);
                        break;
                    case 'ods':
                        $text = self::getTextFromOds($path);
                        break;
                    case 'csv':
                        $text = self::getTextFromCsv($path);
                        break;
                    case 'odt':
                        $text = self::getTextFromOdt($path);
                        break;
                    case 'doc':
                        $text = self::getTextFromDoc($path);
                        break;
                    case 'rtf':
                        $text = self::getTextFromRtf($path);
                        break;
                    case 'xml':
                        $text = self::getTextFromXml($path);
                        break;
                    case 'txt':
                        $text = self::getTextFromTxt($path);
                        break;
                    default:
                        break;
                }
            } 
        } catch (\Exception $ex) {
            
        }
        return $text;
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected static function getTextFromXml($path) {

        $xml = simplexml_load_file($path);
        $text = $xml->getname() . " ";
        $children = $xml->children();
        if (!empty($children)) {
            $text .= self::getChildren($children, $text);
        }
        return $text;
    }

    /**
     * 
     * @param \SimpleXMLElement $children
     * @param string $text
     * @return string
     */
    private static function getChildren($children, $text) {
        foreach ($children as $k => $child) {
            $text .= $k . ": " . $child->__toString() . " \n";
            $children2 = $child->children();
            if (!empty($children2)) {
                $text .= self::getChildren($children2, $text);
            }
        }
        return $text;
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected static function getTextFromPdf($path) {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();

        return $text;
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected static function getTextFromOdt($path) {

        include_once(\Yii::getAlias('@vendor') . '/tinybutstrong/tinybutstrong/tbs_class.php'); // Load the TinyButStrong template engine
        include_once(\Yii::getAlias('@vendor') . '/tinybutstrong/opentbs/tbs_plugin_opentbs.php'); // Load the OpenTBS plugin
        // Carica il file ODT
        $TBS = new \clsTinyButStrong; // new instance of TBS
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load the OpenTBS plugin

        $TBS->LoadTemplate($path, OPENTBS_ALREADY_UTF8); // Also merge some [onload] automatic fields (depends of the type of document).
        $text = (string) strip_tags($TBS->Source);

        return $text;
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected static function getTextFromDocx($path) {
        return self::getTextFromOdt($path);
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected static function getTextFromOds($path) {
        $text = '';

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Ods();

        $spreadsheet = $reader->load($path);

        //leggiamo massimo i primi 10 sheet
        $count = min([$spreadsheet->getSheetCount(), 10]);

        //limitiamo la lettura di massimo 1000 righe
        $limitRows = 1000;
        for ($i = 0; $i < $count; $i++) {
            $worksheet = $spreadsheet->getSheet($i);
            foreach ($worksheet->getRowIterator() as $k => $row) {
                if ($k < $limitRows) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $cell) {
                        $text .= strip_tags($cell->getValue() . ' ');
                    }
                }
            }
        }
        return $text;
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected static function getTextFromXlsx($path) {

        $text = '';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($path);

        //leggiamo massimo i primi 10 sheet
        $count = min([$spreadsheet->getSheetCount(), 10]);

        //limitiamo la lettura di massimo 1000 righe
        $limitRows = 1000;

        for ($i = 0; $i < $count; $i++) {
            $worksheet = $spreadsheet->getSheet($i);
            foreach ($worksheet->getRowIterator() as $k => $row) {
                if ($k < $limitRows) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $cell) {
                        $text .= strip_tags($cell->getValue() . ' ');
                    }
                }
            }
        }
        return $text;
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected static function getTextFromXls($path) {

        $text = '';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $spreadsheet = $reader->load($path);

        //leggiamo massimo i primi 10 sheet
        $count = min([$spreadsheet->getSheetCount(), 10]);

        //limitiamo la lettura di massimo 1000 righe
        $limitRows = 1000;

        for ($i = 0; $i < $count; $i++) {
            $worksheet = $spreadsheet->getSheet($i);
            foreach ($worksheet->getRowIterator() as $k => $row) {
                if ($k < $limitRows) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $cell) {
                        $text .= strip_tags($cell->getValue() . ' ');
                    }
                }
            }
        }
        return $text;
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected static function getTextFromCsv($path) {

        $text = '';

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();

        $encoding = \PhpOffice\PhpSpreadsheet\Reader\Csv::guessEncoding($path);
        $reader->setInputEncoding($encoding);
        $reader->setDelimiter(';');
        $reader->setEnclosure('');
        $reader->setSheetIndex(0);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheet(0);

        // Itera attraverso le righe
        foreach ($sheet->getRowIterator() as $row) {
            // Ottieni le celle della riga
            $cells = $row->getCellIterator();

            // Inizializza un array per contenere il testo delle celle
            // Itera attraverso le celle della riga
            foreach ($cells as $cell) {
                // Ottieni il valore della cella come stringa
                $text .= strip_tags($cell->getValue()) . ' ';
            }
        }

        return $text;
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected static function getTextFromTxt($path) {
        $fh = fopen($path, 'r');

        while ($line = fgets($fh)) {
            $text .= $line;
        }
        fclose($fh);
        $text = preg_replace('/([^\w\d\n\s\t.:,;-_\\\\\/])/mi', '', $text);
        return $text;
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected static function getTextFromDoc($path) {

        $text = '';

        if (file_exists($path)) {
            if (($fh = fopen($path, 'r')) !== false) {
                $headers = fread($fh, 0xA00);
                $n1 = ( ord($headers[0x21C]) - 1 ); // 1 = (ord(n)*1) ; Document has from 0 to 255 characters
                $n2 = ( ( ord($headers[0x21D]) - 8 ) * 256 ); // 1 = ((ord(n)-8)*256) ; Document has from 256 to 63743 characters
                $n3 = ( ( ord($headers[0x21E]) * 256 ) * 256 ); // 1 = ((ord(n)*256)*256) ; Document has from 63744 to 16775423 characters
                $n4 = ( ( ( ord($headers[0x21F]) * 256 ) * 256 ) * 256 ); // 1 = (((ord(n)*256)*256)*256) ; Document has from 16775424 to 4294965504 characters
                /*      $headers = fread($fh, 0x21C);
                  $headers = fread($fh, 0x21D);
                  $headers = fread($fh, 0x21E);
                  $headers = fread($fh, 0x21F); */
                //   $headers = fread($fh, ($n1 + $n2 + $n3 + $n4));
                $textLength = ($n1 + $n2 + $n3 + $n4); // Total length of text in the document*/
                $res_fread = fread($fh, $textLength);
                $extracted_plaintext = mb_convert_encoding($res_fread, 'UTF-8');
                if (empty($extracted_plaintext)) {
                    fread($fh, 0xA00);
                    fread($fh, 0x21C);
                    fread($fh, 0x21D);
                    fread($fh, 0x21E);
                    fread($fh, 0x21F);
                    fread($fh, $textLength);

                    $max = filesize($path);

                    $res_fread = fread($fh, $max);
                    $extracted_plaintext_conv = mb_convert_encoding($res_fread, 'UTF-8');
                    $clearString = preg_replace("/[^a-zA-Z0-9\ \\\\]/", "", $extracted_plaintext_conv);
                    $arrayText = explode(' ', $clearString);
                    foreach ($arrayText as $v) {
                        if (strlen($v) < 20) {
                            $text .= $v . ' ';
                        }
                    }
                    $re = '/(.EMBED)(.*).(\\\\Root)/m';
                    $text = preg_replace($re, '', $text);
                } else {
                    $clearString = preg_replace("/[^a-zA-Z0-9\ \\\\]/", "", $extracted_plaintext);
                    $text = nl2br($clearString);
                }
            }
        }

        return $text;
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected static function getTextFromRtf($path) {
        $reader = new classes\RtfReader();

        $rtf = file_get_contents($path); // or use a string
        $reader->Parse($rtf);
        $formatter = new classes\RtfHtml();
        $textRtf = (string) strip_tags($formatter->Format($reader->root));

        return $textRtf;
    }
    
    public static function purifyText($text) {
        $retValue = self::filterString($text);
        $retValue = str_replace([',', '  ', '.', ';', ':', '(', ')', '[', ']', '{', '}', '!', '?', '£', '$', "\\", '/',
            '%', '&', '=', '^', '*', '§', '°', '#', 'ç', '>', '<', '¿', '-', '_'], ' ', $retValue);
        $retValue = str_replace(['  ', '   ', '    '], ' ', $retValue);
        return $retValue;
    }
    
    public static function filterString($value) {
        $retValue = urldecode(html_entity_decode(strip_tags($value)));
        return $retValue;
    }
}
