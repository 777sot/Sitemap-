<?
// подключаем пролог
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\IO;
use Bitrix\Main\Application;

// массив для категорий и элементов
$array_pages = array();

// простые текстовые страницы
$array_pages[] = array(
    'NAME' => 'Главная страница',
    'URL' => '/',
    'CHANGEFREQ' => 'monthly',
    'PRIORITY' => '0.3',
);
$array_pages[] = array(
    'NAME' => 'Компания',
    'URL' => '/company/contacts/',
    'CHANGEFREQ' => 'monthly',
    'PRIORITY' => '0.3',
);

// ID инфоблоков, разделы и элементы которых попадут в карту сайта
$array_iblocks_id = array(['id_block' => '17', 'changefreq_block' => 'daily1', 'priority_block' => '0.7', 'changefreq_element' => 'always', 'priority_element' => '0.5']);

if (CModule::IncludeModule("iblock")) {
    foreach ($array_iblocks_id as $iblock) {

        // список разделов d7
        $sectionsQuery = new Bitrix\Main\Entity\Query(
            \Bitrix\Iblock\SectionTable::getEntity()
        );
        $sectionsQuery->setSelect(array('ID', 'NAME', 'CODE', 'SECTION_PAGE_URL'  =>  'IBLOCK.SECTION_PAGE_URL'))
            ->setFilter(array('=IBLOCK_ID' => $iblock['id_block'], '=ACTIVE' => "Y"));
        $sections = $sectionsQuery->exec();
        foreach ($sections as $section) {
            $array_pages[] = [
                'NAME' => $section['NAME'],
                'URL' => CIBlock::ReplaceDetailUrl($section['SECTION_PAGE_URL'], $section, true, 'S'),
                'CHANGEFREQ' => $iblock['changefreq_block'],
                'PRIORITY' => $iblock['priority_block'],
            ];
        }

        // // список разделов
        // $res = CIBlockSection::GetList(
        //     array(),
        //     array(
        //         "IBLOCK_ID" => $iblock['id_block'],
        //         "ACTIVE" => "Y",
        //     ),
        //     false,
        //     array(
        //         "ID",
        //         "NAME",
        //         "SECTION_PAGE_URL",
        //     )
        // );
        // while ($ob = $res->GetNext()) {
        //     $array_pages[] = array(
        //         'NAME' => $ob['NAME'],
        //         'URL' => $ob['SECTION_PAGE_URL'],
        //         'CHANGEFREQ' => $iblock['changefreq_block'],
        //         'PRIORITY' => $iblock['priority_block'],
        //     );
        // }

        // cписок элементов d7
        $elementQuery = new Bitrix\Main\Entity\Query(
            \Bitrix\Iblock\ElementTable::getEntity()
        );
        $elementQuery->setSelect(array('ID', 'NAME', 'CODE', 'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL'))
            ->setFilter(array('=IBLOCK_ID' => $iblock['id_block'], '=ACTIVE' => "Y"));
        $elements = $elementQuery->exec();
        foreach ($elements as $element) {
            $array_pages[] = [
                'NAME' => $element['NAME'],
                'URL' => CIBlock::ReplaceDetailUrl($element['DETAIL_PAGE_URL'], $element, true, 'E'),
                'CHANGEFREQ' => $iblock['changefreq_element'],
                'PRIORITY' => $iblock['priority_element'],
            ];
        }

        // // cписок элементов
        // $res = CIBlockElement::GetList(
        //     array(),
        //     array(
        //         "IBLOCK_ID" => $iblock['id_block'],
        //         "ACTIVE" => "Y",
        //     ),
        //     false,
        //     false,
        //     array(
        //         "ID",
        //         "NAME",
        //         "DETAIL_PAGE_URL",
        //     )
        // );
        // while ($ob = $res->GetNext()) {
        //     $array_pages[] = array(
        //         'NAME' => $ob['NAME'],
        //         'URL' => $ob['DETAIL_PAGE_URL'],
        //         'CHANGEFREQ' => $iblock['changefreq_element'],
        //         'PRIORITY' => $iblock['priority_element'],
        //     );
        // }
    }
}

// URL сайта
$site_url = 'https://' . $_SERVER['HTTP_HOST'];

// cоздаём XML документ 
$xml_content = '';
foreach ($array_pages as $key => $value) {
    $xml_content .= '
   	<url>
		<loc>' . $site_url . $value['URL'] . '</loc>
		<lastmod>' . date('Y-m-d') . '</lastmod>
        <changefreq>' . $value['CHANGEFREQ'] . '</changefreq>
        <priority>' . $value['PRIORITY'] . '</priority>
	</url>
	';
}

// подготовка к записи
$xml_file = '<?xml version="1.0" encoding="UTF-8"?>
<urlset>
	' . $xml_content . '
</urlset>
';

// находим/создаем файл для записи
$file = new IO\File(Application::getDocumentRoot() . '/sitemap.xml');

// запись содержимого в файл с заменой
$file->putContents($xml_file);
