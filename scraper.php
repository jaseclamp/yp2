#!/usr/bin/env php
<?


// RedBeanPHP makes the database table for you http://redbeanphp.com/
require 'rb.php';
require 'simple_html_dom.php';  

R::setup('sqlite:data.sqlite');

//should we wipe everything? 
if($wipe = false)
{
    echo "**Just nuking ALL existing DB data**\n";
    R::nuke();
}

$opts = array(
  'http'=>array(
    'method' => "GET",
    'header' => "User-Agent: Lynx/2.8.8dev.3 libwww-FM/2.14 SSL-MM/1.4.1\n\r" .
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.\n\r" .  
                "Accept-Language: en-US,en;q=0.5\n\r" .
                "Accept-Encoding: gzip, deflate\n\r" .
                "Connection: keep-alive"
  )
);

$context = stream_context_create($opts);

function validZip($val){
    
    $zips = array(
    array('2000','2599'),
    array('2619','2898'),
    array('2921','2999'),
    array('2600','2618'),
    array('2900','2920'),
    array('3000','3999'),
    array('4000','4999'),
    array('5000','5799'),
    array('6000','6797'),
    array('7000','7799'),
    array('0800','0899'),
    array('0900','0999')
    );
    
    foreach($zips as $zip)
    	if($val >= $zip[0] AND $val <= $zip[1]) return true; 
    
    return false; 

}

if($recaptchaA=false)
{
    $url = 'https://www.google.com/recaptcha/api/noscript?k=6LdCudcSAAAAACvrzlONXIxL8dTyAlH8op47gdi8&error=null';
    $data = array(
        'recaptcha_challenge_field' => 
        '03AHJ_VuslaAZECgxLfblCOf9O-4Hvkc99B4ZQh7YHCCXjGpq0AxQKNFhTapB2F9Y31VkwhbWVZeFJSvc384ob1hDenHNtHjJztSewzpP_ftguPTzFE7ItYRi8y86tHwftn4kBB5025d65gjCSjAFcPtUNIYoAnoWmUESjOTxlsOunlSJVjVuOQkf_HO0-mHWyB20QVy0vkgATOSHsMv5yQhvS-L7QisXTug', 
        'recaptcha_response_field' => 
        'deduction sshipe');
    
    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n".
                         "User-Agent: Lynx/2.8.8dev.3 libwww-FM/2.14 SSL-MM/1.4.1\n\r" .
                         "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.\n\r" .  
                         "Accept-Language: en-US,en;q=0.5\n\r" .
                         "Accept-Encoding: gzip, deflate\n\r" .
                         "Connection: keep-alive",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );
    $cont  = stream_context_create($options);
    $result = file_get_contents($url, false, $cont);
    
    var_dump($result);
    die;
}

if($recaptchaB=false)
{
    $url = 'http://www.yellowpages.com.au/captcha/submit';
    $data = array(
        'recaptcha_challenge_field' => 
        '03AHJ_VutvuxPOQYqvZCGZpbMbzH5lJSFhJKVilvBbFQxnRRT6dvEAI9fXurermU9u7OU_-J0_e-HY6eKvfquYWdEewAEB96NKCjJV0qMvF523dk2lx1Vp2pYdgizrdMm1PzB96gz309bJUGcpKf9vtg9LVUi5kLbJ46GC2jQqomMb7sMRmr2Z56k', 
        'recaptcha_response_field' => 
        'manual_challenge',
        '_action_submit' =>
        'Submit'
        );
    
    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n".
                         "User-Agent: Lynx/2.8.8dev.3 libwww-FM/2.14 SSL-MM/1.4.1\n\r" .
                         "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.\n\r" .  
                         "Accept-Language: en-US,en;q=0.5\n\r" .
                         "Accept-Encoding: gzip, deflate\n\r" .
                         "Connection: keep-alive",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );
    $cont  = stream_context_create($options);
    $result = file_get_contents($url, false, $cont);
    
    var_dump($result);
    die;
}

function getListings($url,$context){
    
    $r = rand ( 5, 10); 
    echo "\n Pausing ".$r." seconds";
    sleep ( $r ); //throttle! 

    if( ! $html = @file_get_contents($url,null,$context) ) { 
    	echo " -- problem getting page"; 
    	var_dump(get_defined_vars()); 
    	var_dump($http_response_header);
    	die; 
    }
    
    if(strpos($html,"unusual traffic activity")!==false) { 
        
        echo " -- BUSTED!!!"; 
        
        //echo "\n".preg_replace( "/\r|\n/", "", $html );
    
        $dom = new simple_html_dom();
        $dom->load($html);
        
        $url = $dom->find('iframe',0)->src;
        $dom->clear(); unset($dom);
        
        $dom = file_get_html($url);
        
        echo "\nThis is the url: ".$url;
        echo "\nrecaptcha_challenge_field: ".$dom->find('input[id=recaptcha_challenge_field]',0)->value;
        echo "\nimg: https://www.google.com/recaptcha/api/".$dom->find('img',0)->src;
        
        die;
        
    }
    //echo "\n".preg_replace( "/\r|\n/", "", $html );
    
    $dom = new simple_html_dom();
    //$dom->load(gzdecode($html));
    $dom->load($html);
    
    echo "\nMemory usage: ".memory_get_usage();
    //var_dump(get_defined_vars());
    
    unset($html);
    
    $baseurl = "http://www.yellowpages.com.au";
    $dashes['data-business-id'] = 'data-business-id';
    $dashes['data-product-code'] = 'data-product-code';
    $dashes['data-email'] = 'data-email';
    $dashes['data-suburb'] = 'data-suburb';
    $dashes['data-state'] = 'data-state';
            
    foreach( $dom->find('div[class=search-results]',0)->find('div[class=middle-cell]') as $e )
    {
        
        if(!$e->find('a[class=listing-name]',0)) continue; 
        
        else {
            
            echo "\nWorking on: ". $e->find('a[class=listing-name]',0)->innertext;

            $databusinessid = $e->find('div[class=listing-data]',0)->$dashes['data-business-id']; //or should we use data-listing-id as the unique??? 
            
            //have we already processed this link? even recursive safe because parent done last. 
            $result = R::findOne( 'listings', ' databusinessid = ? ', array( $databusinessid ) );
            //if the result is not null that means its already in the db so continue on to the next one in this loop
            if(!is_null($result)) { echo " -- Already done"; continue; }
            unset($result);
            
            $listing = R::dispense('listings');
            
            $listing->databusinessid = $databusinessid;
            unset($databusinessid);
            
            $listing->name = $e->find('a[class=listing-name]',0)->innertext;
            $listing->url = $baseurl . $e->find('a[class=listing-name]',0)->href;
            $listing->dataproductcode = $e->find('div[class=listing-data]',0)->$dashes['data-product-code'];
            
            if($e->find('img[class=listing-logo]',0))
                $listing->logo = $baseurl . $e->find('img[class=listing-logo]',0)->src;
            if($e->find('a[class=contact-phone]',0))
                $listing->phone = $e->find('a[class=contact-phone]',0)->plaintext;
            if($e->find('a[class=contact-url]',0))    
                $listing->website = $e->find('a[class=contact-url]',0)->href;
            if($e->find('a[class=contact-email]',0))
                $listing->email =  $e->find('a[class=contact-email]',0)->$dashes['data-email'];
            $listing->city = $e->find('div[class=listing-data]',0)->$dashes['data-suburb'];
            $listing->state = $e->find('div[class=listing-data]',0)->$dashes['data-state'];
            $listing->address = $e->find('p[class=listing-address]',0)->plaintext;
            
            R::store($listing);
            
            unset($listing);
            
            echo " --  saved ";
            
            
        }
    }
    
    $e->clear();
    unset($e);
    
    //var_dump(get_defined_vars());
    
    //check for another page in pagination and if exist recurse function
    if( $dom->find('div[class=button-pagination-container]',0) )
    if( $dom->find('div[class=button-pagination-container]',0)->find('div[class=circle-glyph]',0) )
    if( $dom->find('div[class=button-pagination-container]',0)->find('div[class=circle-glyph]',0)->next_sibling() )
    if( $dom->find('div[class=button-pagination-container]',0)->find('div[class=circle-glyph]',0)->next_sibling()->tag == 'a' )
    {
        //for some reason the urls on the pagination get screwed up so we have to manually build the url from original
        $nextPageNum = $dom->find('div[class=button-pagination-container]',0)->find('div[class=circle-glyph]',0)->next_sibling()->plaintext; 
        $_url = preg_replace("/(^.*)pageNumber=\d(.*$)/","$1pageNumber=".$nextPageNum."$2",$url);
        echo "\nGetting page number: " . $nextPageNum . " (". $_url. ")";

        $dom->clear();
        unset($dom);
        getListings( $baseurl . $_url  , $context );
    }
    else
    {
        $dom->clear();
        unset($dom);
    }
}

for($i=3000;$i<=9999;$i++)
{
    
    if(!validZip($i)) continue; 
    
    $url = "http://www.yellowpages.com.au/search/listings?clue=Accountants+%26+Auditors&eventType=pagination&locationClue=".$i."&pageNumber=1&referredBy=www.yellowpages.com.au";
    
    echo "\nGETTING ZIP CODE: ".$i." (".$url.")";
    
    getListings($url,$context);
    
} //end for loop

function makeAbsolute($link,$ref) { //ensures a link is absolute if given a baseurl
    if( strpos( $link , parse_url($ref , PHP_URL_HOST ) ) !== false ) 
    {
        return $link; //the base url already exists in the link so return it
    } else {
        $link = preg_replace('/^[\/|\.]*/','',$link); //remove slashes and/or dots from the beginning of the link
        $link = preg_replace('/^[\/|\.]*/','',$link); //do it again to be sure    
        $ref = preg_replace('/\/$/','',$ref); //if there's a slash at the end of ref, remove it and add again in final output to be uniform   
        return $ref . '/' . $link; 
    }
}


function safe($attribname){
    $attribshort = preg_replace("/[^A-Za-z]/","",$attribname); //strip all non alphanumeric and append strlen for uniqueness
    $attribshort = preg_replace_callback('/(([A-Z]{1}))/', function ($matches) { return '_'.strtolower($matches[0]); } , $attribshort ); //replace caps A with _a
    return $attribshort;
}

function updateImgRefs($html,$ref){
    $dom = new simple_html_dom();
    $dom->load($html);
    foreach($dom->find("img") as $e)
        if(strpos($ref,$e->src) === false)
            $e->src = $ref.$e->src;
    $dom->load($dom->save());
    return $dom->save();
}

function strposa($haystack, $needle, $offset=0) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $query) {
        if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
    }
    return false;
}

function url_exists($url){
    if ((strpos($url, "http")) === false) $url = "http://" . $url;
    $headers = @get_headers($url);
    //print_r($headers); die;
    if (is_array($headers)){
        //Check for http error here....should add checks for other errors too...
        if( strpos( $headers[0], '200' ) !== false )
            return true;
        else
            return false;    
    }         
    else
        return false;
}

function stripAttributes($html,$attribs) {
    $dom = new simple_html_dom();
    $dom->load($html);
    foreach($attribs as $attrib)
        foreach($dom->find("*[$attrib]") as $e)
            $e->$attrib = null; 
    $dom->load($dom->save());
    return $dom->save();
}

function stripEmptyTags($html) {
    $dom = new simple_html_dom();
    $dom->load($html);
    foreach($dom->find("*") as $e)
        if( trim( str_replace( array(' ','&nbsp;'), "", $e->innertext )) == "" ) 
            $e->outertext = "";
    $dom->load($dom->save());
    return $dom->save();
}

function stripTagsByCount($html,$tag,$count=99999999999999) { //strip first x tags. html is plain text, tag is one tag e.g. 'p', count is how many to remove before stopping. 
    $dom = new simple_html_dom();
    $dom->load($html);
    $c=0;
    foreach( $dom->find($tag) as $e )
    {
        if( $c < $count )
            $e->outertext = "";
        $c++;
    }
    $dom->load($dom->save());
    return $dom->save();
}

function stripTagsByContent( $html, $tag, $content='' ) { //strip tag only if it contains x content
    $dom = new simple_html_dom();
    $dom->load($html);
    foreach( $dom->find($tag) as $e )
    {
        if( strpos($e->outertext,$content)!==false ) $e->outertext = "";
    }
    $dom->load( $dom->save() );
    return $dom->save( );
}

function stripTags($str, $tags, $stripContent = false) { //this is like strip_tags but it is inclusive instead of exclusive
    $content = '';
    if(!is_array($tags)) {
        $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
        if(end($tags) == '') array_pop($tags);
    }
    foreach($tags as $tag) {
        if ($stripContent)
             $content = '(.+</'.$tag.'[^>]*>|)';
         $str = preg_replace('#</?'.$tag.'[^>]*>'.$content.'#is', '', $str);
    }
    return $str;
}

function filterText($text,$wipe=false) { //spits out only html safe text

	//UTF-8 filter
		$conv = array(
		      //jase custom finds...
		      "\xEF\xBC\x8E" => '&bull;',
              "\xC2\xA0" => '&nbsp;',
              "\xC2\xA1" => '&iexcl;',
              "\xC2\xA2" => '&cent;',
              "\xC2\xA3" => '&pound;',
              "\xC2\xA4" => '&curren;',
              "\xC2\xA5" => '&yen;',
              "\xC2\xA6" => '&brvbar;',
              "\xC2\xA7" => '&sect;',
              "\xC2\xA8" => '&uml;',
              "\xC2\xA9" => '&copy;',
              "\xC2\xAA" => '&ordf;',
              "\xC2\xAB" => '&laquo;',
              "\xC2\xAC" => '&not;',
              "\xC2\xAD" => '&shy;',
              "\xC2\xAE" => '&reg;',
              "\xC2\xAF" => '&macr;',
              "\xC2\xB0" => '&deg;',
              "\xC2\xB1" => '&plusmn;',
              "\xC2\xB2" => '&sup2;',
              "\xC2\xB3" => '&sup3;',
              "\xC2\xB4" => '&acute;',
              "\xC2\xB5" => '&micro;',
              "\xC2\xB6" => '&para;',
              "\xC2\xB7" => '&middot;',
              "\xC2\xB8" => '&cedil;',
              "\xC2\xB9" => '&sup1;',
              "\xC2\xBA" => '&ordm;',
              "\xC2\xBB" => '&raquo;',
              "\xC2\xBC" => '&frac14;',
              "\xC2\xBD" => '&frac12;',
              "\xC2\xBE" => '&frac34;',
              "\xC2\xBF" => '&iquest;',
              "\xC3\x80" => '&Agrave;',
              "\xC3\x81" => '&Aacute;',
              "\xC3\x82" => '&Acirc;',
              "\xC3\x83" => '&Atilde;',
              "\xC3\x84" => '&Auml;',
              "\xC3\x85" => '&Aring;',
              "\xC3\x86" => '&AElig;',
              "\xC3\x87" => '&Ccedil;',
              "\xC3\x88" => '&Egrave;',
              "\xC3\x89" => '&Eacute;',
              "\xC3\x8A" => '&Ecirc;',
              "\xC3\x8B" => '&Euml;',
              "\xC3\x8C" => '&Igrave;',
              "\xC3\x8D" => '&Iacute;',
              "\xC3\x8E" => '&Icirc;',
              "\xC3\x8F" => '&Iuml;',
              "\xC3\x90" => '&ETH;',
              "\xC3\x91" => '&Ntilde;',
              "\xC3\x92" => '&Ograve;',
              "\xC3\x93" => '&Oacute;',
              "\xC3\x94" => '&Ocirc;',
              "\xC3\x95" => '&Otilde;',
              "\xC3\x96" => '&Ouml;',
              "\xC3\x97" => '&times;',
              "\xC3\x98" => '&Oslash;',
              "\xC3\x99" => '&Ugrave;',
              "\xC3\x9A" => '&Uacute;',
              "\xC3\x9B" => '&Ucirc;',
              "\xC3\x9C" => '&Uuml;',
              "\xC3\x9D" => '&Yacute;',
              "\xC3\x9E" => '&THORN;',
              "\xC3\x9F" => '&szlig;',
              "\xC3\xA0" => '&agrave;',
              "\xC3\xA1" => '&aacute;',
              "\xC3\xA2" => '&acirc;',
              "\xC3\xA3" => '&atilde;',
              "\xC3\xA4" => '&auml;',
              "\xC3\xA5" => '&aring;',
              "\xC3\xA6" => '&aelig;',
              "\xC3\xA7" => '&ccedil;',
              "\xC3\xA8" => '&egrave;',
              "\xC3\xA9" => '&eacute;',
              "\xC3\xAA" => '&ecirc;',
              "\xC3\xAB" => '&euml;',
              "\xC3\xAC" => '&igrave;',
              "\xC3\xAD" => '&iacute;',
              "\xC3\xAE" => '&icirc;',
              "\xC3\xAF" => '&iuml;',
              "\xC3\xB0" => '&eth;',
              "\xC3\xB1" => '&ntilde;',
              "\xC3\xB2" => '&ograve;',
              "\xC3\xB3" => '&oacute;',
              "\xC3\xB4" => '&ocirc;',
              "\xC3\xB5" => '&otilde;',
              "\xC3\xB6" => '&ouml;',
              "\xC3\xB7" => '&divide;',
              "\xC3\xB8" => '&oslash;',
              "\xC3\xB9" => '&ugrave;',
              "\xC3\xBA" => '&uacute;',
              "\xC3\xBB" => '&ucirc;',
              "\xC3\xBC" => '&uuml;',
              "\xC3\xBD" => '&yacute;',
              "\xC3\xBE" => '&thorn;',
              "\xC3\xBF" => '&yuml;',
              // Latin Extended-A
              "\xC5\x92" => '&OElig;',
              "\xC5\x93" => '&oelig;',
              "\xC5\xA0" => '&Scaron;',
              "\xC5\xA1" => '&scaron;',
              "\xC5\xB8" => '&Yuml;',
              // Spacing Modifier Letters
              "\xCB\x86" => '&circ;',
              "\xCB\x9C" => '&tilde;',
              // General Punctuation
              "\xE2\x80\x82" => '&ensp;',
              "\xE2\x80\x83" => '&emsp;',
              "\xE2\x80\x89" => '&thinsp;',
              "\xE2\x80\x8C" => '&zwnj;',
              "\xE2\x80\x8D" => '&zwj;',
              "\xE2\x80\x8E" => '&lrm;',
              "\xE2\x80\x8F" => '&rlm;',
              "\xE2\x80\x93" => '&ndash;',
              "\xE2\x80\x94" => '&mdash;',
              "\xE2\x80\x98" => '&lsquo;',
              "\xE2\x80\x99" => '&rsquo;',
              "\xE2\x80\x9A" => '&sbquo;',
              "\xE2\x80\x9C" => '&ldquo;',
              "\xE2\x80\x9D" => '&rdquo;',
              "\xE2\x80\x9E" => '&bdquo;',
              "\xE2\x80\xA0" => '&dagger;',
              "\xE2\x80\xA1" => '&Dagger;',
              "\xE2\x80\xB0" => '&permil;',
              "\xE2\x80\xB9" => '&lsaquo;',
              "\xE2\x80\xBA" => '&rsaquo;',
              "\xE2\x82\xAC" => '&euro;',
              // Latin Extended-B
              "\xC6\x92" => '&fnof;',
              // Greek
              "\xCE\x91" => '&Alpha;',
              "\xCE\x92" => '&Beta;',
              "\xCE\x93" => '&Gamma;',
              "\xCE\x94" => '&Delta;',
              "\xCE\x95" => '&Epsilon;',
              "\xCE\x96" => '&Zeta;',
              "\xCE\x97" => '&Eta;',
              "\xCE\x98" => '&Theta;',
              "\xCE\x99" => '&Iota;',
              "\xCE\x9A" => '&Kappa;',
              "\xCE\x9B" => '&Lambda;',
              "\xCE\x9C" => '&Mu;',
              "\xCE\x9D" => '&Nu;',
              "\xCE\x9E" => '&Xi;',
              "\xCE\x9F" => '&Omicron;',
              "\xCE\xA0" => '&Pi;',
              "\xCE\xA1" => '&Rho;',
              "\xCE\xA3" => '&Sigma;',
              "\xCE\xA4" => '&Tau;',
              "\xCE\xA5" => '&Upsilon;',
              "\xCE\xA6" => '&Phi;',
              "\xCE\xA7" => '&Chi;',
              "\xCE\xA8" => '&Psi;',
              "\xCE\xA9" => '&Omega;',
              "\xCE\xB1" => '&alpha;',
              "\xCE\xB2" => '&beta;',
              "\xCE\xB3" => '&gamma;',
              "\xCE\xB4" => '&delta;',
              "\xCE\xB5" => '&epsilon;',
              "\xCE\xB6" => '&zeta;',
              "\xCE\xB7" => '&eta;',
              "\xCE\xB8" => '&theta;',
              "\xCE\xB9" => '&iota;',
              "\xCE\xBA" => '&kappa;',
              "\xCE\xBB" => '&lambda;',
              "\xCE\xBC" => '&mu;',
              "\xCE\xBD" => '&nu;',
              "\xCE\xBE" => '&xi;',
              "\xCE\xBF" => '&omicron;',
              "\xCF\x80" => '&pi;',
              "\xCF\x81" => '&rho;',
              "\xCF\x82" => '&sigmaf;',
              "\xCF\x83" => '&sigma;',
              "\xCF\x84" => '&tau;',
              "\xCF\x85" => '&upsilon;',
              "\xCF\x86" => '&phi;',
              "\xCF\x87" => '&chi;',
              "\xCF\x88" => '&psi;',
              "\xCF\x89" => '&omega;',
              "\xCF\x91" => '&thetasym;',
              "\xCF\x92" => '&upsih;',
              "\xCF\x96" => '&piv;',
              // General Punctuation
              "\xE2\x80\xA2" => '&bull;',
              "\xE2\x80\xA6" => '&hellip;',
              "\xE2\x80\xB2" => '&prime;',
              "\xE2\x80\xB3" => '&Prime;',
              "\xE2\x80\xBE" => '&oline;',
              "\xE2\x81\x84" => '&frasl;',
              // Letterlike Symbols
              "\xE2\x84\x98" => '&weierp;',
              "\xE2\x84\x91" => '&image;',
              "\xE2\x84\x9C" => '&real;',
              "\xE2\x84\xA2" => '&trade;',
              "\xE2\x84\xB5" => '&alefsym;',
              // Arrows
              "\xE2\x86\x90" => '&larr;',
              "\xE2\x86\x91" => '&uarr;',
              "\xE2\x86\x92" => '&rarr;',
              "\xE2\x86\x93" => '&darr;',
              "\xE2\x86\x94" => '&harr;',
              "\xE2\x86\xB5" => '&crarr;',
              "\xE2\x87\x90" => '&lArr;',
              "\xE2\x87\x91" => '&uArr;',
              "\xE2\x87\x92" => '&rArr;',
              "\xE2\x87\x93" => '&dArr;',
              "\xE2\x87\x94" => '&hArr;',
              // Mathematical Operators
              "\xE2\x88\x80" => '&forall;',
              "\xE2\x88\x82" => '&part;',
              "\xE2\x88\x83" => '&exist;',
              "\xE2\x88\x85" => '&empty;',
              "\xE2\x88\x87" => '&nabla;',
              "\xE2\x88\x88" => '&isin;',
              "\xE2\x88\x89" => '&notin;',
              "\xE2\x88\x8B" => '&ni;',
              "\xE2\x88\x8F" => '&prod;',
              "\xE2\x88\x91" => '&sum;',
              "\xE2\x88\x92" => '&minus;',
              "\xE2\x88\x97" => '&lowast;',
              "\xE2\x88\x9A" => '&radic;',
              "\xE2\x88\x9D" => '&prop;',
              "\xE2\x88\x9E" => '&infin;',
              "\xE2\x88\xA0" => '&ang;',
              "\xE2\x88\xA7" => '&and;',
              "\xE2\x88\xA8" => '&or;',
              "\xE2\x88\xA9" => '&cap;',
              "\xE2\x88\xAA" => '&cup;',
              "\xE2\x88\xAB" => '&int;',
              "\xE2\x88\xB4" => '&there4;',
              "\xE2\x88\xBC" => '&sim;',
              "\xE2\x89\x85" => '&cong;',
              "\xE2\x89\x88" => '&asymp;',
              "\xE2\x89\xA0" => '&ne;',
              "\xE2\x89\xA1" => '&equiv;',
              "\xE2\x89\xA4" => '&le;',
              "\xE2\x89\xA5" => '&ge;',
              "\xE2\x8A\x82" => '&sub;',
              "\xE2\x8A\x83" => '&sup;',
              "\xE2\x8A\x84" => '&nsub;',
              "\xE2\x8A\x86" => '&sube;',
              "\xE2\x8A\x87" => '&supe;',
              "\xE2\x8A\x95" => '&oplus;',
              "\xE2\x8A\x97" => '&otimes;',
              "\xE2\x8A\xA5" => '&perp;',
              "\xE2\x8B\x85" => '&sdot;',
              // Miscellaneous Technical
              "\xE2\x8C\x88" => '&lceil;',
              "\xE2\x8C\x89" => '&rceil;',
              "\xE2\x8C\x8A" => '&lfloor;',
              "\xE2\x8C\x8B" => '&rfloor;',
              "\xE2\x8C\xA9" => '&lang;',
              "\xE2\x8C\xAA" => '&rang;',
              // Geometric Shapes
              "\xE2\x97\x8A" => '&loz;',
              "\xe2\x97\x8f" => '&bull;',
              // Miscellaneous Symbols
              "\xE2\x99\xA0" => '&spades;',
              "\xE2\x99\xA3" => '&clubs;',
              "\xE2\x99\xA5" => '&hearts;',
              "\xE2\x99\xA6" => '&diams;'
       );

        if($wipe)
        {
            foreach ($conv as $key => $value) $_conv[$key] = ''; 
            $string = strtr($text, $_conv); 
        }else{
            $string = strtr($text, $conv); 
        }
        
        //kill stragglers
		$string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $string);
		
		//new translate any unicode stuff... 
		$conv = array(
			chr(128) => "&euro;",
			chr(129) => "",
			chr(130) => "&sbquo;",
			chr(131) => "&fnof;",
			chr(132) => "&bdquo;",
			chr(133) => "&hellip;",
			chr(134) => "&dagger;",
			chr(135) => "&Dagger;",
			chr(136) => "&circ;",
			chr(137) => "&permil;",
			chr(138) => "&Scaron;",
			chr(139) => "&lsaquo;",
			chr(140) => "&OElig;",
			chr(141) => "",
			chr(142) => "",
			chr(143) => "", //this is an A with a circle at top but we just kill those... 
			chr(144) => "",
			chr(145) => "&lsquo;",
			chr(146) => "&rsquo;",
			chr(147) => "&ldquo;",
			chr(148) => "&rdquo;",
			chr(149) => "&bull;",
			chr(150) => "&ndash;",
			chr(151) => "&mdash;",
			chr(152) => "&tilde;",
			chr(153) => "&trade;",
			chr(154) => "&scaron;",
			chr(155) => "&rsaquo;",
			chr(156) => "&oelig;",
			chr(157) => "",
			chr(158) => "",
			chr(159) => "&yuml;",
			chr(160) => "&nbsp;",
			chr(161) => "&iexcl;",
			chr(162) => "&cent;",
			chr(163) => "&pound;",
			chr(164) => "&curren;",
			chr(165) => "&yen;",
			chr(166) => "&brvbar;",
			chr(167) => "&sect;",
			chr(168) => "&uml;",
			chr(169) => "&copy;",
			chr(170) => "&ordf;",
			chr(171) => "&laquo;",
			chr(172) => "&not;",
			chr(173) => "&shy;",
			chr(174) => "&reg;",
			chr(175) => "&macr;",
			chr(176) => "&deg;",
			chr(177) => "&plusmn;",
			chr(178) => "&sup2;",
			chr(179) => "&sup3;",
			chr(180) => "&acute;",
			chr(181) => "&micro;",
			chr(182) => "&para;",
			chr(183) => "&middot;",
			chr(184) => "&cedil;",
			chr(185) => "&sup1;",
			chr(186) => "&ordm;",
			chr(187) => "&raquo;",
			chr(188) => "&frac14;",
			chr(189) => "&frac12;",
			chr(190) => "&frac34;",
			chr(191) => "&iquest;",
			chr(192) => "&Agrave;",
			chr(193) => "&Aacute;",
			chr(194) => "&Acirc;",
			chr(195) => "&Atilde;",
			chr(196) => "&Auml;",
			chr(197) => "&Aring;",
			chr(198) => "&AElig;",
			chr(199) => "&Ccedil;",
			chr(200) => "&Egrave;",
			chr(201) => "&Eacute;",
			chr(202) => "&Ecirc;",
			chr(203) => "&Euml;",
			chr(204) => "&Igrave;",
			chr(205) => "&Iacute;",
			chr(206) => "&Icirc;",
			chr(207) => "&Iuml;",
			chr(208) => "&ETH;",
			chr(209) => "&Ntilde;",
			chr(210) => "&Ograve;",
			chr(211) => "&Oacute;",
			chr(212) => "&Ocirc;",
			chr(213) => "&Otilde;",
			chr(214) => "&Ouml;",
			chr(215) => "&times;",
			chr(216) => "&Oslash;",
			chr(217) => "&Ugrave;",
			chr(218) => "&Uacute;",
			chr(219) => "&Ucirc;",
			chr(220) => "&Uuml;",
			chr(221) => "&Yacute;",
			chr(222) => "&THORN;",
			chr(223) => "&szlig;",
			chr(224) => "&agrave;",
			chr(225) => "&aacute;",
			chr(226) => "&acirc;",
			chr(227) => "&atilde;",
			chr(228) => "&auml;",
			chr(229) => "&aring;",
			chr(230) => "&aelig;",
			chr(231) => "&ccedil;",
			chr(232) => "&egrave;",
			chr(233) => "&eacute;",
			chr(234) => "&ecirc;",
			chr(235) => "&euml;",
			chr(236) => "&igrave;",
			chr(237) => "&iacute;",
			chr(238) => "&icirc;",
			chr(239) => "&iuml;",
			chr(240) => "&eth;",
			chr(241) => "&ntilde;",
			chr(242) => "&ograve;",
			chr(243) => "&oacute;",
			chr(244) => "&ocirc;",
			chr(245) => "&otilde;",
			chr(246) => "&ouml;",
			chr(247) => "&divide;",
			chr(248) => "&oslash;",
			chr(249) => "&ugrave;",
			chr(250) => "&uacute;",
			chr(251) => "&ucirc;",
			chr(252) => "&uuml;",
			chr(253) => "&yacute;",
			chr(254) => "&thorn;",
			chr(255) => "&yuml;"
		);

        if($wipe)
        {
            foreach ($conv as $key => $value) $_conv[$key] = ''; 
            return strtr($string, $_conv); 
        }else{
            return strtr($string, $conv); 
        }

} 

?>

