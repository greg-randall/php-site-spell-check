<?php
include 'dict.php'; // array called $dictionary.

$ignore = array(
		
);


$page = file_get_contents ("https://gobama.ua.edu/steps/transfer/");

$page=preg_replace( "/\r|\n/", "", $page);
$page=preg_replace( "/\s+/", " ", $page);
$page=str_replace(array(">","<"),array(">\n","\n<"),$page);
$page=preg_replace( "/>[\s\n\r]+</", "><", $page);
$page=str_replace("\n ","\n",$page);

//echo $page;


$lines=explode("\n",$page);

foreach ($lines as &$line) {
	
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
	/*	*/
				
}


function clean_word($word){
	
	$clean_word=strtoupper($word); //make string uppercase
	$clean_word=trim($clean_word);
	if(!ctype_alnum(substr($clean_word,-2,1))){
		$clean_word=substr($clean_word,0,-2);			
	}
	$clean_word=html_entity_decode($clean_word);
	$clean_word=preg_replace("/[^\w]/", '', $clean_word); //remove all non letters
	$clean_word=preg_replace("/[0-9]+/", '', $clean_word); //remove all non letters
	return($clean_word);
}
?>