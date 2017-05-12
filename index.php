<?php
include 'dict.php'; // array called $dictionary.

$spell_check_base_url="http://localhost:8888/spell-check/index.php?p=";

$url=$_GET["p"];

$page = file_get_contents ($url);

$page=preg_replace( "/\r|\n/", "", $page);
$page=preg_replace( "/\s+/", " ", $page);
$page=str_replace(array(">","<"),array(">\n","\n<"),$page);
//$page=preg_replace( "/>[\s\n\r]+</", "><", $page);
$page=str_replace("\n ","\n",$page);

$lines=explode("\n",$page);

foreach ($lines as &$line) {
	
	if(substr($line,0,3)=="<a "){
		if(!strstr($line,'href="http')){	
			$line = str_replace('href="','note="relative-link" href="'.$url.'',$line);
		}
		$line = str_replace('href="','href="'.$spell_check_base_url,$line);
	}
	
	if($line[0]!="<"&&substr($line,0,3)!="-->"){
		$words=explode(" ",$line);
		foreach ($words as &$word) {
			if(isset($dict[clean_word($word)])|clean_word($word)==""){
				echo "$word ";
			}
			else{
				echo "\n<span style='background-color:red;' note='".clean_word($word)."'>$word</span> \n";
			}
		}
		
	}else{
		echo "\n".$line."\n";
	}			
}


function clean_word($word){
	
	$clean_word=strtoupper($word); //make string uppercase
	$clean_word=trim($clean_word); //remove leading/trailing whitespace
	if(!ctype_alnum(substr($clean_word,-2,1))&&substr($clean_word,-1,1)=="S"){//if word has apostophie and a 's' (ie your's) drop those charecters
		$clean_word=substr($clean_word,0,-2);			
	}
	$clean_word=html_entity_decode($clean_word); //decode the html encoded stuff to regular letters (ie &nbsp; to " ")
	$clean_word=preg_replace("/[^\w]/", '', $clean_word); //remove all non letters
	$clean_word=preg_replace("/[0-9]+/", '', $clean_word); //remove all non letters
	return($clean_word);
}
?>