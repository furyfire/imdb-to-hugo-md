<?php

include "vendor/autoload.php";


const ESCAPEES = ['\\', '\\\\', '\\"', '"',
"\x00",  "\x01",  "\x02",  "\x03",  "\x04",  "\x05",  "\x06",  "\x07",
"\x08",  "\x09",  "\x0a",  "\x0b",  "\x0c",  "\x0d",  "\x0e",  "\x0f",
"\x10",  "\x11",  "\x12",  "\x13",  "\x14",  "\x15",  "\x16",  "\x17",
"\x18",  "\x19",  "\x1a",  "\x1b",  "\x1c",  "\x1d",  "\x1e",  "\x1f",
"\x7f",
"\xc2\x85", "\xc2\xa0", "\xe2\x80\xa8", "\xe2\x80\xa9",
];
const ESCAPED = ['\\\\', '\\"', '\\\\', '\\"',
'\\0',   '\\x01', '\\x02', '\\x03', '\\x04', '\\x05', '\\x06', '\\a',
'\\b',   '\\t',   '\\n',   '\\v',   '\\f',   '\\r',   '\\x0e', '\\x0f',
'\\x10', '\\x11', '\\x12', '\\x13', '\\x14', '\\x15', '\\x16', '\\x17',
'\\x18', '\\x19', '\\x1a', '\\e',   '\\x1c', '\\x1d', '\\x1e', '\\x1f',
'\\x7f',
'\\N', '\\_', '\\L', '\\P',
];

function yaml_escape($instance, string $value, $charset): string
{
    return str_replace(ESCAPEES, ESCAPED, $value);
}


$climate = new League\CLImate\CLImate;

//load the CSV document from a file path

if($argc != 3)
{
    $climate->error("Please provide an input CSV file and an output folder");
    die -1;
}
$csv = League\Csv\Reader::createFromPath($argv[1], 'r');
$csv->setHeaderOffset(0);

$loader = new \Twig\Loader\FilesystemLoader('.');
$twig = new \Twig\Environment($loader, ['autoescape'=>false]);
$twig->getExtension(\Twig\Extension\EscaperExtension::class)->setEscaper('yaml', 'yaml_escape');

$config = new \Imdb\Config();
$config->language = 'en';

$progress = $climate->progress()->total($csv->count());
$genres = array();
foreach($csv as $index=>$rating)
{
    //Progress bar
    $title = $rating['Title'];
    $progress->current($index, $rating['Title']);
    //echo "Processing ($index/$count) - $title \n";
    
    //Create IMDbPHP instance
    $imdb = new \Imdb\Title($rating['Const'], $config);

    //Titles not parsed correctly if they contain '
    $title = $imdb->orig_title() ? $imdb->orig_title() : $imdb->title();
    $title = html_entity_decode($title, ENT_QUOTES|ENT_HTML5);
    
    //Add genres to full list
    $genres = array_unique(array_merge($genres, $imdb->genres()));

    //Render through Twig
    $file_contents = $twig->render('template.md', ['title' => $title, 'movie' => $imdb, 'rating'=>$rating]);
    //Write to File
    $filename = $argv[2].'/'.$rating['Const'].'.md';
    file_put_contents($filename, $file_contents);
    unset($imdb, $title);
}

$climate->json($genres);
