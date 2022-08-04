<?php

date_default_timezone_set('Europe/Prague');

$tarif = ( isset( $_GET['tarif'] ) && intval( $_GET['tarif'] ) ) ? $_GET['tarif'] : 490;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user_agents = array(
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
    'Mozilla/5.0 (iPhone; CPU iPhone OS 14_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/87.0.4280.77 Mobile/15E148 Safari/604.1',
    'Mozilla/5.0 (Linux; Android 10; SM-A205U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.101 Mobile Safari/537.36',
    'Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.101 Mobile Safari/537.36',
    'Mozilla/5.0 (Linux; Android 8.0.0; SM-G960F Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.84 Mobile Safari/537.36',
    'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
    'Mozilla/5.0 (iPhone9,3; U; CPU iPhone OS 10_0_1 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) Version/10.0 Mobile/14A403 Safari/602.1'
);

$url = 'https://www.predistribuce.cz/cs/potrebuji-zaridit/zakaznici/stav-hdo/';
$file = 'cache_' . md5( $url . $tarif ) . '.html';

if ( file_exists( $file ) && filemtime( $file ) > time() - 600 ) {

    $result = file_get_contents( $file );

} else {

    $ch = curl_init( $url );
    curl_setopt_array( $ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_ENCODING       => 'gzip, deflate',
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_REFERER        => $url,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_MAXREDIRS      => 10,
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => $user_agents[array_rand($user_agents)],
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_POSTFIELDS     => "povel=" . $tarif . "&___povel=" . $tarif . "@F&action=submit",
        CURLOPT_HTTPHEADER     => array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
        )
    ) );

    $result = curl_exec( $ch );
    $header = curl_getinfo( $ch );
    curl_close( $ch );

    file_put_contents( $file, $result );
}

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
@$dom->loadHTML( $result );
$xpath = new DOMXPath( $dom );

$time_current = date('H:i');
//header('Content-type: application/json');

foreach( $xpath->query( "//div[@id='component-hdo-dnes']//div[@class='hdo-bar']//span" ) as $node ) {
    if ( $node->getAttribute("class") === 'hdont' ) {

        $nextelement = $xpath->query("following-sibling::*[1]", $node);

        if ( empty( $nextelement ) )
            continue;

        if ( empty( $nextelement[0]->getAttribute("title") ) )
            continue;

        $time_range = explode( ' - ' , $nextelement[0]->getAttribute("title") );

        if ( !empty( $time_range ) && is_array( $time_range ) ) {

            if ( $time_current >= $time_range[0] && $time_current < $time_range[1] ) {
                echo 1;
                die();
            }

        }
    }
}

echo 0;
die();

?>
